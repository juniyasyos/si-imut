<?php

namespace App\Http\Controllers\Api;

use App\Services\GreetingService;
use Illuminate\Http\JsonResponse;

class GreetingController
{
    public function __construct(
        protected GreetingService $greetingService
    ) {}

    /**
     * Get greeting data
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->greetingService->getGreetingData(),
        ]);
    }

    /**
     * Get random quote by time
     */
    public function quote(?string $timeKey = null): JsonResponse
    {
        $quote = $this->greetingService->getRandomQuote($timeKey);

        return response()->json([
            'success' => true,
            'data' => [
                'quote' => $quote,
                'time_key' => $timeKey ?? $this->greetingService->getTimeKey(),
            ],
        ]);
    }

    /**
     * Get all quotes
     */
    public function quotes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->greetingService->getAllQuotes(),
        ]);
    }
}
