<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Set AI_DEFAULT_PROVIDER in your .env to switch between providers.
    | Supported: "openai", "anthropic", "groq", "gemini", "deepseek",
    |            "mistral", "ollama", "openrouter", "xai", "azure"
    |
    */

    'default' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The model to use when none is explicitly specified by an agent.
    | Examples:
    |   OpenAI:    gpt-4o, gpt-4o-mini, gpt-4.1
    |   Anthropic: claude-opus-4-5, claude-haiku-4-5-20251001
    |   Groq:      llama-3.3-70b-versatile, mixtral-8x7b-32768
    |   Gemini:    gemini-2.0-flash, gemini-1.5-pro
    |   DeepSeek:  deepseek-chat, deepseek-reasoner
    |
    */

    'default_model' => env('AI_DEFAULT_MODEL', 'gpt-4o-mini'),

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */

    'caching' => [
        'embeddings' => [
            'cache' => false,
            'store' => env('CACHE_STORE', 'database'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Configure API keys for each provider via environment variables.
    | Only the provider set as AI_DEFAULT_PROVIDER needs a key.
    |
    */

    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'key'    => env('OPENAI_API_KEY'),
            'url'    => env('OPENAI_URL', 'https://api.openai.com/v1'),
        ],

        'anthropic' => [
            'driver' => 'anthropic',
            'key'    => env('ANTHROPIC_API_KEY'),
            'url'    => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1'),
        ],

        'groq' => [
            'driver' => 'groq',
            'key'    => env('GROQ_API_KEY'),
            'url'    => env('GROQ_URL', 'https://api.groq.com/openai/v1'),
        ],

        'gemini' => [
            'driver' => 'gemini',
            'key'    => env('GEMINI_API_KEY'),
        ],

        'deepseek' => [
            'driver' => 'deepseek',
            'key'    => env('DEEPSEEK_API_KEY'),
        ],

        'mistral' => [
            'driver' => 'mistral',
            'key'    => env('MISTRAL_API_KEY'),
            'url'    => env('MISTRAL_URL', 'https://api.mistral.ai/v1'),
        ],

        'ollama' => [
            'driver' => 'ollama',
            'key'    => env('OLLAMA_API_KEY', ''),
            'url'    => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        ],

        'openrouter' => [
            'driver' => 'openrouter',
            'key'    => env('OPENROUTER_API_KEY'),
        ],

        'xai' => [
            'driver' => 'xai',
            'key'    => env('XAI_API_KEY'),
            'url'    => env('XAI_URL', 'https://api.x.ai/v1'),
        ],

        'azure' => [
            'driver'               => 'azure',
            'key'                  => env('AZURE_OPENAI_API_KEY'),
            'url'                  => env('AZURE_OPENAI_URL'),
            'api_version'          => env('AZURE_OPENAI_API_VERSION', '2024-10-21'),
            'deployment'           => env('AZURE_OPENAI_DEPLOYMENT', 'gpt-4o'),
            'embedding_deployment' => env('AZURE_OPENAI_EMBEDDING_DEPLOYMENT', 'text-embedding-3-small'),
        ],
    ],

];
