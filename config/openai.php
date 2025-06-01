<?php

declare(strict_types=1);

return [

    /**
     * Whether the AI is enabled.
     */
    'enabled' => env('OPENAI_ENABLED', false),

    /**
     * The model to use.
     */
    'model' => env('OPENAI_MODEL', 'o4-mini'),

    /**
     * The API key to use.
     */
    'api_key' => env('OPENAI_API_KEY'),

    /**
     * The URL to use.
     */
    'url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
];
