@php
    use Illuminate\Support\Str;
    use App\Services\GreetingService;

    $user = filament()->auth()->user();
    $displayName = Str::limit(filament()->getUserName($user), 24);

    $greetingData = app(GreetingService::class)->getGreetingData();
    $greeting = $greetingData['greeting'];
    $quote = $greetingData['quote'];

    $heroImage = asset('images/assets/doctor-hero.png');
@endphp

<x-filament-widgets::widget style="margin-top: -14vh">
    {{-- wrapper transparan --}}
    <div class="relative overflow-hidden rounded-3xl">

        {{-- background biru absolute (full width, 3/4 tinggi) --}}
        <div aria-hidden="true"
            class="absolute inset-x-0 bottom-0
                   rounded-2xl bg-[#DDE6FB] dark:bg-slate-700/80
                   ring-1 ring-black/5 dark:ring-white/10" style="height: 60%">

            {{-- background pattern halus --}}
            <div
                class="pointer-events-none absolute inset-0 rounded-2xl
                        bg-[radial-gradient(900px_400px_at_20%_-10%,rgba(255,255,255,.6),transparent),
                            radial-gradient(900px_300px_at_80%_120%,rgba(255,255,255,.45),transparent)]
                        dark:bg-[radial-gradient(900px_360px_at_25%_-10%,rgba(148,163,184,.12),transparent),
                            radial-gradient(800px_320px_at_80%_120%,rgba(148,163,184,.1),transparent)]
                        bg-fixed">
            </div>
        </div>

        {{-- konten utama (relative agar di atas background) --}}
        <div class="relative px-6 py-10">
            <div class="relative grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
                {{-- teks kiri --}}
                <div class="col-span-1 md:col-span-3 z-10">
                    <h2
                        class="font-extrabold tracking-tight
                               text-[#0b4b4b] dark:text-white
                               leading-tight
                               text-[clamp(12px,2.0vw,24px)]">
                        {{ $greeting }}, {{ $displayName }} <span>👋</span>
                    </h2>

                    <p
                        class="mt-2 text-slate-700/90 dark:text-slate-300
                               leading-snug
                               text-[clamp(8px,1.8vw,14px)]">
                        {{ $quote }}
                    </p>

                    {{-- badge tanggal/jam --}}
                    <div class="mt-4 inline-grid grid-flow-col auto-cols-max gap-2">
                        <span
                            class="inline-grid grid-flow-col auto-cols-max items-center gap-1
                                     rounded-full bg-white/60 dark:bg-white/10
                                     px-3 py-1 ring-1 ring-black/5 dark:ring-white/10
                                     text-slate-700 dark:text-slate-300
                                     text-[clamp(9px,1.8vw,12px)]">
                            @svg("heroicon-m-calendar", "w-[1em] h-[1em]")
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                        <span
                            class="inline-grid grid-flow-col auto-cols-max items-center gap-1
                                     rounded-full bg-white/60 dark:bg-white/10
                                     px-3 py-1 ring-1 ring-black/5 dark:ring-white/10
                                     text-slate-700 dark:text-slate-300
                                     text-[clamp(9px,1.8vw,12px)]">
                            @svg("heroicon-m-clock", "w-[1em] h-[1em]")
                            {{ now()->format('H:i') }} WIB
                        </span>
                    </div>
                </div>

                {{-- gambar kanan (relative flow) --}}
                <div class="col-span-1 md:col-span-2 flex justify-end items-end -mb-10 mr-3"
                    style="
                        --size: 16vw;     /* ukuran dasar yang mengikuti viewport */
                        --k: 1.1;         /* pengali tambahan biar gampang diatur */
                        --min: 180px;     /* batas minimal biar nggak terlalu kecil */
                        --max: 360px;     /* batas maksimal biar nggak kebesaran */
                    ">
                    <img src="{{ $heroImage }}" alt="Ilustrasi tenaga medis"
                        class="w-[calc(var(--size)*var(--k))]
                                max-w-[var(--max)]
                                min-w-[var(--min)]
                                h-auto object-contain select-none pointer-events-none
                                drop-shadow-sm"
                        loading="lazy" />
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
