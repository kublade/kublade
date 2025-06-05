<div>
    @if ($show)
        <div class="position-fixed bottom-0 end-0 h-100 d-flex flex-column gap-3" style="width: 600px; max-width: 100%;">
            <div class="card border border-end-0 border-top-0 border-bottom-0 rounded-0 border-secondary flex-grow-1 h-100 shadow-lg">
                <div class="card-header d-flex align-items-center justify-content-between gap-3 rounded-0" style="min-height: 5rem;max-height: 5rem;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-robot fs-5"></i>
                        {{ __('AI Companion') }}
                    </div>
                    <button class="bg-transparent border-0 outline-0 text-white" wire:click="toggle">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="card-body d-flex flex-column gap-3 flex-shrink-1 overflow-hidden">
                    <select wire:model.live="chatId" class="form-select" wire:change="setChat">
                        <option value="">{{ __('Select a chat...') }}</option>
                        <option value="new">{{ __('New Chat') }}</option>
                        @foreach ($chats as $chat)
                            <option value="{{ $chat->id }}" @selected($chatId == $chat->id)>{{ $chat->created_at->format('d/m/Y H:i') }} ({{ $chat->id }})</option>
                        @endforeach
                    </select>
                
                    <div id="chat-messages" class="border p-4 rounded overflow-auto overflow-y-scroll flex-grow-1">
                        @foreach (filterChatMessages($messages) as $message)
                            @if ($message['role'] !== 'system')
                                @if ($message['role'] === 'user')
                                    <div class="mb-2 bg-secondary text-white p-3 rounded me-auto ms-5 ai-chat-message">
                                        {!! processChatContext($message['content']) !!}
                                    </div>
                                @else
                                    <div class="mb-2 bg-primary text-white p-3 rounded ms-auto me-5 ai-chat-message">
                                        {!! processChatContent($message['content'], $mode, $templateId) !!}
                                    </div>
                                @endif
                            @else
                                @if ($systemPrompts)
                                    <div class="mb-2 bg-info text-white p-3 rounded me-auto ms-5 ai-chat-message">
                                        {!! processChatContext($message['content']) !!}
                                    </div>
                                @endif
                            @endif
                        @endforeach

                        @if ($sending)
                            <div class="mb-2 bg-primary text-white p-3 rounded ms-auto me-5 d-flex align-items-center gap-3">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                {{ __('Thinking...') }}
                            </div>
                        @endif
                    </div>

                    @if ($contextSet)
                        @if (\App\Helpers\AI\Context::tokenCountExceeded($messages))
                            <div class="alert alert-warning p-4 rounded mb-0 d-flex align-items-center gap-3">
                                <i class="bi bi-exclamation-triangle fs-5"></i>
                                <div>
                                    <strong>{{ __('Warning:') }}</strong> {{ __('The conversation exceeds the maximum number of tokens. Old messages will not be transmitted. This may result in a partial loss of context.') }}
                                </div>
                            </div>
                        @endif

                        <div class="alert alert-secondary p-4 rounded mb-0 d-flex align-items-center gap-3">
                            <i class="bi bi-robot fs-5"></i>
                            <div>
                                <strong>{{ __('Disclaimer:') }}</strong> {{ __('AI responses are automatically generated and may be incorrect or incomplete. Please verify the information before using it.') }}
                            </div>
                        </div>

                        <form wire:submit.prevent="sendMessage" class="input-group">
                            <select class="form-select flex-shrink-1 ai-chat-mode" name="mode" id="mode" wire:model.live="mode">
                                <option value="ask">{{ __('Ask') }}</option>
                                <option value="agent">{{ __('Agent') }}</option>
                            </select>
                            <input type="text" wire:model="userInput" class="form-control" placeholder="Type your message..." @keydown.enter.prevent="$wire.sendMessage()" />
                            <button type="submit" class="btn btn-secondary">Send</button>
                        </form>
                        
                        @if (config('app.env') !== 'production')
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="checkbox" name="systemPrompts" id="systemPrompts" wire:model.live="systemPrompts">

                                        <label class="form-check-label lh-1" for="systemPrompts">
                                            {{ __('Show system prompts') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @else
        <button class="btn btn-secondary btn-lg rounded position-fixed bottom-0 end-0 m-3 w-15 h-15 d-flex align-items-center justify-content-center{{ $hidden ? ' d-none' : '' }}" wire:click="toggle">
            <i class="bi bi-robot fs-5"></i>
        </button>
    @endif
</div>

<script type="text/javascript">
    function scrollToBottom() {
        const chatMessages = document.querySelector('#chat-messages');
            
        if (chatMessages) {
            requestAnimationFrame(() => {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
        }
    }

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('chatChanged', () => {
            setTimeout(scrollToBottom);
        });
    });
</script>
@if ($show)
    <script type="text/javascript">
        scrollToBottom();
    </script>
@endif
