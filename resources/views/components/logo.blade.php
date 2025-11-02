@props(['width' => '200'])

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 170" width="{{ $width }}" height="{{ $width * 0.28 }}"
    role="img" aria-labelledby="logo-title logo-desc" {{ $attributes }}>
    <title id="logo-title">SI-IMUT — Sistem Informasi Indikator Mutu Terintegrasi</title>
    <desc id="logo-desc">Ikon diagram melingkar dengan tanda centang, teks SI-IMUT di sisi kanan.</desc>

    <defs>
        <style>
            .logo-primary {
                stroke: #0F4CAD;
            }

            .logo-accent {
                stroke: #00A78E;
            }

            .logo-muted {
                stroke: #94A3B8;
            }

            .logo-text {
                fill: #0F172A;
            }

            /* Dark mode styles */
            .dark .logo-primary {
                stroke: #60A5FA;
            }

            .dark .logo-accent {
                stroke: #34D399;
            }

            .dark .logo-muted {
                stroke: #334155;
            }

            .dark .logo-text {
                fill: #F8FAFC;
            }

            .logo-text-element {
                font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            }
        </style>
    </defs>

    <!-- ========= ICON (left) ========= -->
    <g transform="translate(30,20)">
        <!-- Basis ring tipis -->
        <circle cx="70" cy="70" r="46" fill="none" class="logo-muted" stroke-width="12"
            opacity="0.5" />

        <!-- Arc biru (progress utama) -->
        <g transform="rotate(-35 70 70)">
            <circle cx="70" cy="70" r="46" fill="none" class="logo-primary" stroke-width="12"
                stroke-linecap="round" stroke-dasharray="180 400" />
        </g>

        <!-- Arc hijau kecil (komponen/segment lain) -->
        <g transform="rotate(135 70 70)">
            <circle cx="70" cy="70" r="46" fill="none" class="logo-accent" stroke-width="12"
                stroke-linecap="round" stroke-dasharray="70 400" />
        </g>

        <!-- Checkmark di tengah -->
        <path d="M52 70 l14 14 28-30" fill="none" class="logo-accent" stroke-width="12" stroke-linecap="round"
            stroke-linejoin="round" />
    </g>

    <!-- ========= TEXT (right) ========= -->
    <g transform="translate(185,50)">
        <text x="0" y="50" font-size="70" font-weight="600" class="logo-text logo-text-element"
            letter-spacing="-0.5">SI-IMUT</text>
        <text x="0" y="90" font-size="30" font-weight="400" class="logo-text logo-text-element" opacity="0.9">
            Sistem Informasi Indikator Mutu
        </text>
    </g>
</svg>
