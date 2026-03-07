<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GreetingService
{
    /**
     * Get greeting message based on time
     */
    public function getGreeting(): string
    {
        $hour = now()->format('H');

        return match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    /**
     * Get time key based on current hour
     */
    public function getTimeKey(): string
    {
        $hour = now()->format('H');

        return match (true) {
            $hour >= 0 && $hour < 4 => 'dini',
            $hour < 11 => 'pagi',
            $hour < 15 => 'siang',
            $hour < 18 => 'sore',
            $hour < 22 => 'malam',
            default => 'larut',
        };
    }

    /**
     * Get all quotes from JSON file
     */
    public function getAllQuotes(): array
    {
        return Cache::remember('greeting_quotes', now()->addDay(), function () {
            $path = public_path('quotes/greeting-quotes.json');

            if (!File::exists($path)) {
                return [];
            }

            $content = File::get($path);
            return json_decode($content, true) ?? [];
        });
    }

    /**
     * Get random quote based on time
     */
    public function getRandomQuote(?string $timeKey = null): string
    {
        $timeKey = $timeKey ?? $this->getTimeKey();
        $quotes = $this->getAllQuotes();

        if (empty($quotes[$timeKey])) {
            return 'Semangat terus untuk hari ini! 💪';
        }

        return $quotes[$timeKey][array_rand($quotes[$timeKey])];
    }

    /**
     * Get greeting data for widget
     */
    public function getGreetingData(): array
    {
        return [
            'greeting' => $this->getGreeting(),
            'quote' => $this->getRandomQuote(),
            'time_key' => $this->getTimeKey(),
        ];
    }
}
