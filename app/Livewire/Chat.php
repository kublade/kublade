<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helpers\AI\Context;
use App\Models\AI\AiChat;
use App\Models\AI\AiChatMessage;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Class Chat.
 *
 * This class is the chat component.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Chat extends Component
{
    public Collection $chats;

    public ?string $chatId = '';

    public ?AiChat $chat = null;

    public $messages = [];

    public $userInput = '';

    public $contextSet = false;

    public $show = false;

    public $sending = false;

    public $hidden;

    public $mode = 'ask';

    public $systemPrompts = false;

    public $templateId = null;

    public ?string $routeName = null;

    public array $routeParameters = [];

    /**
     * Toggle system prompts visibility.
     */
    public function updatedSystemPrompts()
    {
        $this->dispatch('chatChanged');
    }

    /**
     * Toggle mode.
     */
    public function updatedMode()
    {
        Cookie::queue('ai_mode', $this->mode, 360);

        $this->dispatch('chatChanged');
    }

    /**
     * Mount the component.
     *
     * @param Route|null $route
     */
    public function mount(Route $route = null)
    {
        $route = $route ?? request()->route();

        $this->templateId = $route->parameter('template_id');
        $this->mode       = Cookie::get('ai_mode') ?? 'ask';
        $this->chatId     = Cookie::get('ai_chat_id') ?? '';

        if ($route) {
            $this->routeName       = $route->getName();
            $this->routeParameters = $route->parameters();
        }

        $this->hidden = !config('ai.enabled', false);
        $this->show   = session('from') === 'ai';

        $this->chats = AiChat::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        if ($this->chatId && $this->chatId !== 'new') {
            $this->hydrateChat();
        } else {
            $this->chatId     = '';
            $this->chat       = null;
            $this->messages   = [];
            $this->contextSet = false;
        }
    }

    /**
     * Hydrate the chat.
     */
    public function hydrateChat()
    {
        if ($this->chatId && $this->chatId !== 'new') {
            $this->chat = AiChat::find($this->chatId);

            if ($this->chat) {
                $this->messages = $this->chat->messages()->get()->map(function (AiChatMessage $message) {
                    return [
                        'role'    => $message->role,
                        'content' => $message->content,
                    ];
                })->toArray();

                $this->contextSet = true;
            }
        }
    }

    /**
     * Set the chat.
     */
    public function setChat()
    {
        if ($this->chatId === 'new') {
            $this->chat = AiChat::create([
                'user_id' => Auth::id(),
            ]);

            $this->chats = AiChat::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->get();
            $this->chatId   = (string) $this->chat->id;
            $this->messages = [];

            $this->setContext();
        } elseif ($this->chatId === '') {
            $this->chatId     = '';
            $this->chat       = null;
            $this->messages   = [];
            $this->contextSet = false;
        } else {
            $this->hydrateChat();
            $this->resetRouteContext($this->routeName);
        }

        $this->dispatch('chatChanged');
    }

    /**
     * Set the context.
     */
    private function setContext()
    {
        $message = [
            'role'    => 'system',
            'content' => Context::getContext('all', [
                'name'       => $this->routeName,
                'parameters' => $this->routeParameters,
            ], false),
        ];

        $this->messages[] = $message;

        $this->chat->messages()->create($message);

        $this->contextSet = true;
    }

    /**
     * Reset the route context.
     *
     * @param string $routeName
     */
    private function resetRouteContext(string $routeName)
    {
        $routeContext = collect($this->messages)
            ->reverse()
            ->where('role', 'system')
            ->first();

        $hasContext = array_key_exists('content', $routeContext ?? []) && Str::contains($routeContext['content'], 'The current route is ' . $routeName . '.');

        if (!$hasContext) {
            $message = [
                'role'    => 'system',
                'content' => Context::getContext('route', [
                    'name'       => $this->routeName,
                    'parameters' => $this->routeParameters,
                ], true),
            ];

            $this->messages[] = $message;

            $this->chat->messages()->create($message);
        }
    }

    /**
     * Send the message.
     */
    public function sendMessage()
    {
        if (!$this->chat) {
            $this->hydrateChat();
        }

        if (trim($this->userInput) === '') {
            return;
        }

        $userMessage     = $this->userInput;
        $this->userInput = '';

        $message = [
            'role'    => 'user',
            'content' => $userMessage,
        ];

        $this->messages[] = $message;

        $this->chat->messages()->create($message);

        $this->sending = true;

        $this->dispatch('process-message', message: $userMessage);
        $this->dispatch('chatChanged');
    }

    /**
     * Process the message.
     *
     * @param string $message
     */
    #[On('process-message')]
    public function processMessage(string $message)
    {
        if (!$this->chat) {
            $this->hydrateChat();
        }

        try {
            $response = Http::withToken(config('ai.api_key'))->post(config('ai.url') . '/chat/completions', [
                'model'    => config('ai.model'),
                'messages' => $this->messages,
            ]);

            $reply = $response['choices'][0]['message']['content'] ?? 'No response';

            $message = [
                'role'    => 'assistant',
                'content' => $reply,
            ];

            $this->messages[] = $message;

        } catch (Exception $e) {
            $message = [
                'role'    => 'assistant',
                'content' => 'Sorry, there was an error processing your request.',
            ];
        } finally {
            $this->chat->messages()->create($message);

            $this->sending = false;

            $this->dispatch('chatChanged');
        }
    }

    /**
     * Toggle the chat.
     */
    public function toggle()
    {
        $this->show = !$this->show;

        if (
            $this->show &&
            $this->chatId !== '' &&
            $this->chatId !== 'new'
        ) {
            $this->dispatch('chatChanged');
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.chat', [
            'show' => $this->show,
        ]);
    }

    public function updatedChatId($value)
    {
        Cookie::queue('ai_chat_id', $value, 360);
    }
}
