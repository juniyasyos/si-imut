<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

/**
 * Custom Filament field: TextInput dengan autocomplete dari history suggestions.
 * 
 * UX: ketik → dropdown muncul → ↑↓ navigate → Enter confirm.
 * Jika input baru (tidak ada di history) → langsung diterima dan disimpan ke history.
 */
class AutocompleteTextInput extends Field
{
    protected string $view = 'forms.components.autocomplete-text-input';

    /**
     * Daftar suggestions dari history (array of strings)
     */
    protected array $suggestions = [];

    /**
     * Callback yang dipanggil saat user input nilai baru (tidak ada di history)
     */
    protected $onNewValueCallback = null;

    /**
     * Mode preview — tidak menyimpan ke history
     */
    protected bool $previewMode = false;

    // ─── Fluent setters ───────────────────────────────────────────────────────

    public function suggestions(array $suggestions): static
    {
        $this->suggestions = array_values(array_filter($suggestions));
        return $this;
    }

    public function onNewValue(?callable $callback): static
    {
        $this->onNewValueCallback = $callback;
        return $this;
    }

    public function previewMode(bool $preview = true): static
    {
        $this->previewMode = $preview;
        return $this;
    }

    // ─── Getters (diakses dari Blade) ─────────────────────────────────────────

    public function getSuggestions(): array
    {
        return $this->suggestions;
    }

    public function isPreviewMode(): bool
    {
        return $this->previewMode;
    }

    // ─── Lifecycle ────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();

        // Saat state berubah → cek apakah nilai baru, lalu trigger callback
        $this->afterStateUpdated(function ($state) {
            if (
                $state !== null &&
                $state !== '' &&
                !$this->previewMode &&
                $this->onNewValueCallback !== null
            ) {
                ($this->onNewValueCallback)($state);
            }
        });
    }
}
