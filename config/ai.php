<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API (bulk email templates, etc.)
    |--------------------------------------------------------------------------
    |
    | Uses the OpenAI Chat Completions API. Also works with compatible
    | providers if you change OPENAI_BASE_URL (e.g. Groq, Azure OpenAI).
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    'base_url' => rtrim(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/'),

    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),

    'fallback_models' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OPENAI_FALLBACK_MODELS', 'gpt-4o-mini,gpt-4.1-mini'))
    ))),

    'timeout' => (int) env('OPENAI_TIMEOUT', 60),

    'retries' => (int) env('OPENAI_RETRIES', 2),

];
