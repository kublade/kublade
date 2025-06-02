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
];
