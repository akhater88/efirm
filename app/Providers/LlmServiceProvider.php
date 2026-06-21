<?php

namespace App\Providers;

use App\Llm\AnthropicProvider;
use App\Llm\LlmProvider;
use App\Llm\MockProvider;
use Illuminate\Support\ServiceProvider;

class LlmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LlmProvider::class, function () {
            $provider = config('llm.provider', 'anthropic');

            return match ($provider) {
                'mock' => new MockProvider,
                'anthropic' => new AnthropicProvider,
                default => new MockProvider,
            };
        });
    }
}
