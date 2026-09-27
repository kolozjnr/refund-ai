<?php
namespace App\Providers;

use App\Services\AI\AIProvider;
use App\Services\AI\GeminiProvider;
use App\Services\AI\OpenRouterProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AIProvider::class, function ($app) {
            return new class($app->make(GeminiProvider::class), $app->make(OpenRouterProvider::class)) implements AIProvider {
                public function __construct(private AIProvider $primary, private AIProvider $fallback) {}

                public function analyze(string $message, array $orderContext): array
                {
                    try {
                        return $this->primary->analyze($message, $orderContext);
                    } catch (\Throwable $exception) {
                        \Illuminate\Support\Facades\Log::warning('Gemini analysis failed; trying OpenRouter fallback.', ['exception' => $exception::class, 'message' => $exception->getMessage()]);
                        return $this->fallback->analyze($message, $orderContext);
                    }
                }
            };
        });
    }
    public function boot(): void {}
}
