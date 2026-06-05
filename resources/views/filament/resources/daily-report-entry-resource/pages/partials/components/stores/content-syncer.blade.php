{{-- 
    Content Syncer Store
    Synchronizes selected date between Livewire and Alpine.js
    
    Purpose:
    - Watch Livewire selectedDate changes
    - Validate date values
    - Provide fallback to current date
--}}

x-data="{
    contentSelectedDate: @entangle('selectedDate'),
    
    init() {
        // Watch for Livewire selectedDate changes and sync to Alpine
        this.\$watch('contentSelectedDate', (newDate) => {
            // Ensure we don't accept null or invalid dates
            if (newDate && newDate !== 'null' && newDate !== '') {
                this.selectedDate = newDate;
            } else {
                // If Livewire sends null/empty, keep current selectedDate or use today
                if (!this.selectedDate || this.selectedDate === 'null' || this.selectedDate === '') {
                    this.selectedDate = '{{ now()->format('Y-m-d') }}';
                }
            }
        });
        
        // Initialize with current selectedDate, but validate it
        if (this.contentSelectedDate && this.contentSelectedDate !== 'null' && this.contentSelectedDate !== '') {
            this.selectedDate = this.contentSelectedDate;
        } else {
            this.selectedDate = '{{ now()->format('Y-m-d') }}';
        }
    }
}"
