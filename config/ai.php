<?php

declare(strict_types=1);

return [

    /**
     * Whether the AI is enabled.
     */
    'enabled' => env('AI_ENABLED', false),

    /**
     * The model to use.
     */
    'model' => env('AI_MODEL', 'o4-mini'),

    /**
     * The API key to use.
     */
    'api_key' => env('AI_API_KEY'),

    /**
     * The URL to use.
     */
    'url' => env('AI_URL', 'https://api.openai.com/v1'),

    /**
     * The maximum number of tokens to use.
     * This depends on the model used. For o4-mini, the maximum is 128000.
     * To keep it safe, we set it to 100000 by default.
     * Exceeding the maximum number of tokens will result in context truncation.
     */
    'max_tokens' => env('AI_MAX_TOKENS', 100000),
];
