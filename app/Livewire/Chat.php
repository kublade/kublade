<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Exceptions\AiException;
use App\Helpers\AI\Context;
use App\Models\AI\AiChat;
use App\Models\AI\AiChatMessage;
use App\Services\AI\McpHandler;
use App\Services\AI\VectorRouter;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
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

        if (!AiChat::find($this->chatId)) {
            $this->chatId = '';
            Cookie::forget('ai_chat_id');
        }

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
            $this->resetRouteContext($this->routeName);
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
                        'role'      => $message->role,
                        'content'   => $message->content,
                        'key'       => $message->key,
                        'protected' => $message->protected,
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
        // System message for the basic agent setup
        $message = [
            'role'      => 'system',
            'content'   => Context::getContext('all'),
            'key'       => null,
            'protected' => true,
        ];

        $this->messages[] = $message;

        AiChatMessage::create([
            'ai_chat_id' => $this->chat->id,
            ...$message,
        ]);

        // System message for the route context
        $message = [
            'role'    => 'system',
            'content' => Context::getContext('route', [
                'name'       => $this->routeName,
                'parameters' => $this->routeParameters,
            ]),
            'key'       => 'route',
            'protected' => false,
        ];

        $this->messages[] = $message;

        AiChatMessage::create([
            'ai_chat_id' => $this->chat->id,
            ...$message,
        ]);

        // Indicate that the context setup is complete
        $this->contextSet = true;
    }

    /**
     * Reset the route context.
     *
     * @param string $routeName
     */
    private function resetRouteContext(string $routeName)
    {
        $message = [
            'role'    => 'system',
            'content' => Context::getContext('route', [
                'name'       => $this->routeName,
                'parameters' => $this->routeParameters,
            ]),
            'key'       => 'route',
            'protected' => false,
        ];

        $this->messages[] = $message;

        AiChatMessage::create([
            'ai_chat_id' => $this->chat->id,
            ...$message,
        ]);
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
            'role'      => 'user',
            'content'   => $userMessage,
            'key'       => null,
            'protected' => false,
        ];

        $this->messages[] = $message;

        AiChatMessage::create([
            'ai_chat_id' => $this->chat->id,
            ...$message,
        ]);

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
            try {
                $vectorRouter = new VectorRouter(config('ai.prompt_routing_vectors_file'), true);
                $vectors      = $vectorRouter->getVectorsFromPrompt($message);
                $nearest      = $vectorRouter->findNearest($vectors->embedding);

                if (!$nearest) {
                    throw new AiException('No nearest vector found', 404);
                }

                $mcpHandler = new McpHandler(
                    $nearest->action,
                    $message,
                    Context::extractSystemMessages(
                        Context::ensureTokenCount(
                            Context::filterDuplicateContext($this->messages)
                        )
                    )
                );

                $reply = $mcpHandler->handle();

                $message = [
                    'role'      => 'system',
                    'content'   => $reply,
                    'key'       => 'mcp',
                    'protected' => false,
                ];

                $this->messages[] = $message;

                AiChatMessage::create([
                    'ai_chat_id' => $this->chat->id,
                    ...$message,
                ]);
            } catch (Exception $e) {
                /**
                 * Silently ignore the error and continue with the normal flow.
                 * A missing MCP call is not a critical error. It just means that
                 * some additional, user defined context may be missing.
                 */
            }

            $response = Http::withToken(config('ai.api_key'))->post(config('ai.url') . config('ai.chat_completions_endpoint'), [
                'model'    => config('ai.model'),
                'messages' => Context::prepareSubmission(
                    Context::ensureTokenCount(
                        Context::filterDuplicateContext($this->messages)
                    )
                ),
            ]);

            $reply = $response['choices'][0]['message']['content'];

            if (empty($reply)) {
                throw new AiException('No response from the API', 500);
            }

            $message = [
                'role'      => 'assistant',
                'content'   => $reply,
                'key'       => null,
                'protected' => false,
            ];
        } catch (Exception $e) {
            $message = [
                'role'      => 'assistant',
                'content'   => 'Sorry, there was an error processing your request.',
                'key'       => null,
                'protected' => false,
            ];
        }

        $this->messages[] = $message;

        AiChatMessage::create([
            'ai_chat_id' => $this->chat->id,
            ...$message,
        ]);

        $this->sending = false;

        $this->dispatch('chatChanged');
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

    /**
     * Updated the chat id.
     *
     * @param string $value
     */
    public function updatedChatId($value)
    {
        Cookie::queue('ai_chat_id', $value, 360);
    }
}
