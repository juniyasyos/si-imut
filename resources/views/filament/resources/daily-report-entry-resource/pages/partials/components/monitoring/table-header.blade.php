<thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 sticky top-0 z-10">
    <tr>
        <th class="sticky left-0 z-20 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 px-6 py-4 text-left text-sm font-bold text-gray-800 dark:text-gray-200 border-r-2 border-b-2 border-gray-300 dark:border-gray-600 min-w-[300px] shadow-sm">
            <div class="flex items-center gap-2">
                @svg("heroicon-m-clipboard-document-list", "w-5 h-5 text-gray-600 dark:text-gray-400")
                <span>Indikator Mutu</span>
            </div>
        </th>

        @foreach($daysInMonth as $day)
        @php
        $isToday = $this->isToday($day);
        $dayOfWeek = \Carbon\Carbon::parse($selectedMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT))->dayOfWeek;
        $isWeekend = in_array($dayOfWeek, [0]); // 0=Sunday
        @endphp
        <th class="px-4 py-3 border-b-2 border-gray-300 dark:border-gray-600 min-w-[100px] {{ $isWeekend ? 'bg-gray-100 dark:bg-slate-800/80' : '' }}">
            <div class="flex flex-col items-center gap-1
                {{ $isToday
                    ? 'bg-primary-100 dark:bg-primary-900/40 rounded-lg py-1.5 px-2 ring-2 ring-primary-400 dark:ring-primary-600'
                    : ''
                }}">
                <span class="text-base font-bold {{ $isToday ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300' }}">
                    {{ $day }}
                </span>

                @if($isToday)
                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-primary-600 text-white shadow-sm">
                    HARI INI
                </span>
                @else
                <span class="text-[10px] font-medium {{ $isWeekend ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                    {{ ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][$dayOfWeek] }}
                </span>
                @endif
            </div>
        </th>
        @endforeach
    </tr>
</thead>