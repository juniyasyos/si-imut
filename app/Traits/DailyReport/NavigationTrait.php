<?php

namespace App\Traits\DailyReport;

use Carbon\Carbon;
use App\Services\DailyReport\CachedSettingsService;

trait NavigationTrait
{
    /**
     * Filter logic methods
     */
    public function isToday(int $day): bool
    {
        $today = now();
        $cellDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->day($day);
        return $cellDate->isSameDay($today);
    }

    public function isInWeek(int $day): bool
    {
        $realToday = now();
        $backDays = CachedSettingsService::getBackDataEntryDays();
        $start = $realToday->copy()->subDays($backDays)->startOfDay();
        $cellDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->day($day)->startOfDay();

        $inRange = $cellDate->between($start, $realToday->copy()->endOfDay());

        $currentMonth = Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $realMonth = $realToday->copy()->startOfMonth();

        if ($currentMonth->isSameMonth($realMonth)) {
            return $inRange;
        }

        if ($cellDate->lt($realToday)) {
            $daysDiff = $realToday->diffInDays($cellDate);
            return $daysDiff <= $backDays;
        }

        return false;
    }

    public function isInMonth(int $day): bool
    {
        $today = now();
        $cellDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->day($day);
        return $cellDate->isSameMonth($today);
    }

    public function shouldShowCell(int $day, string $filterPeriod): bool
    {
        $period = $filterPeriod ?? $this->filterPeriod;
        return match ($period) {
            'today' => $this->isToday($day),
            'weekly' => $this->isInWeek($day),
            'monthly' => $this->isInMonth($day),
            default => true
        };
    }

    public function setFilterPeriod(string $period): void
    {
        $this->filterPeriod = $period;
    }

    /**
     * Check if can go to next month
     */
    public function canGoNextMonth(): bool
    {
        $currentMonth = Carbon::parse($this->selectedMonth . '-01');
        $thisMonth = now()->startOfMonth();
        return $currentMonth->isBefore($thisMonth);
    }

    /**
     * Select date from navigation
     */
    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;

        // Sync month with selected date
        $selectedDateMonth = Carbon::parse($date)->format('Y-m');
        if ($this->selectedMonth !== $selectedDateMonth) {
            $this->selectedMonth = $selectedDateMonth;
            $this->loadMatrixData();
        }

        // Close slide over if open
        if ($this->slideOverOpen) {
            $this->closeSlideOver();
        }

        // Emit date selected event for other components
        $this->dispatch('dateSelected', date: $date);

        $this->dispatch('matrixSnapshotUpdated', snapshot: $this->getMatrixSnapshot());

        // Update browser URL to reflect selected date (for bookmarking/filtering)
        try {
            $params = http_build_query(['selectedMonth' => $this->selectedMonth, 'selectedDate' => $date]);
            $newUrl = request()->url() . '?' . $params;
            \Illuminate\Support\Facades\Log::info('selectDate URL update', [
                'selectedMonth' => $this->selectedMonth,
                'selectedDate' => $date,
                'params' => $params,
                'newUrl' => $newUrl,
            ]);
            // Use dispatch() instead of dispatchBrowserEvent() for Livewire v3
            $this->dispatch('updateUrl', url: $newUrl);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('selectDate URL update failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Navigate to previous month
     */
    public function previousMonth(): void
    {
        $date = Carbon::parse($this->selectedMonth . '-01')->subMonth();
        $this->selectedMonth = $date->format('Y-m');
        
        // Set selectedDate to first day of new month if not explicitly set
        if (!$this->selectedDate) {
            $this->selectedDate = $date->format('Y-m-d');
        }
        
        $this->loadMatrixData();
        $this->dispatch('matrixSnapshotUpdated', snapshot: $this->getMatrixSnapshot());
        // Update URL to include month/date
        try {
            $params = http_build_query(['selectedMonth' => $this->selectedMonth, 'selectedDate' => $this->selectedDate]);
            $newUrl = request()->url() . '?' . $params;
            \Illuminate\Support\Facades\Log::info('previousMonth URL update', [
                'selectedMonth' => $this->selectedMonth,
                'selectedDate' => $this->selectedDate,
                'params' => $params,
                'newUrl' => $newUrl,
            ]);
            // Use dispatch() instead of dispatchBrowserEvent() for Livewire v3
            $this->dispatch('updateUrl', url: $newUrl);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('previousMonth URL update failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Navigate to next month
     */
    public function nextMonth(): void
    {
        if (!$this->canGoNextMonth()) {
            return;
        }

        $date = Carbon::parse($this->selectedMonth . '-01')->addMonth();
        $this->selectedMonth = $date->format('Y-m');
        
        // Set selectedDate to first day of new month if not explicitly set
        if (!$this->selectedDate) {
            $this->selectedDate = $date->format('Y-m-d');
        }
        
        $this->loadMatrixData();
        $this->dispatch('matrixSnapshotUpdated', snapshot: $this->getMatrixSnapshot());
        // Update URL to include month/date
        try {
            $params = http_build_query(['selectedMonth' => $this->selectedMonth, 'selectedDate' => $this->selectedDate]);
            $newUrl = request()->url() . '?' . $params;
            \Illuminate\Support\Facades\Log::info('nextMonth URL update', [
                'selectedMonth' => $this->selectedMonth,
                'selectedDate' => $this->selectedDate,
                'params' => $params,
                'newUrl' => $newUrl,
            ]);
            // Use dispatch() instead of dispatchBrowserEvent() for Livewire v3
            $this->dispatch('updateUrl', url: $newUrl);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('nextMonth URL update failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle date selection from frontend
     */
    public function handleDateSelected(string $date): void
    {
        $this->selectedDate = $date;
    }
}
