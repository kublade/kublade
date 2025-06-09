<?php

declare(strict_types=1);

return [

    /**
     * Whether the AI is enabled.
     */
    'enabled' => env('AI_ENABLED', false),

    /**
     * The model to use for chat completions.
     */
    'model' => env('AI_MODEL', 'o4-mini'),

    /**
     * The endpoint to use for chat completions.
     */
    'chat_completions_endpoint' => env('AI_CHAT_COMPLETIONS_ENDPOINT', '/v1/chat/completions'),

    /**
     * The model to use for embeddings.
     */
    'embedding_model' => env('AI_EMBEDDING_MODEL', 'text-embedding-3-small'),

    /**
     * The endpoint to use for embeddings.
     */
    'embedding_endpoint' => env('AI_EMBEDDING_ENDPOINT', '/v1/embeddings'),

    /**
     * Whether to use remote embedding.
     */
    'remote_embedding' => env('AI_REMOTE_EMBEDDING', true),

    /**
     * The API key to use.
     */
    'api_key' => env('AI_API_KEY'),

    /**
     * The URL to use.
     */
    'url' => env('AI_URL', 'https://api.openai.com'),

    /**
     * The maximum number of tokens to use.
     * This depends on the model used. For o4-mini, the maximum is 128000.
     * To keep it safe, we set it to 100000 by default.
     * Exceeding the maximum number of tokens will result in context truncation.
     */
    'max_tokens' => env('AI_MAX_TOKENS', 100000),

    /**
     * The path to the prompt routing vectors.
     */
    'prompt_routing_vectors_file' => env('AI_PROMPT_ROUTING_VECTORS_FILE', '/ai/prompt-routing-vectors.json'),
];
