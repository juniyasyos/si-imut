@props(['width' => '96'])

<svg xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 200 280"
    width="{{ $width }}"
    height="{{ $width * 1.4 }}"
    role="img"
    aria-labelledby="logo-report-title logo-report-desc"
    {{ $attributes }}>

    <title id="logo-report-title">SI-IMUT — Sistem Informasi Indikator Mutu Terintegrasi</title>
    <desc id="logo-report-desc">Ikon indikator mutu terintegrasi untuk laporan.</desc>

    <defs>
        <style>
            /* ===== LIGHT MODE ===== */
            .icon-primary {
                stroke: #0F4CAD;
            }

            .icon-accent {
                stroke: #00A78E;
            }

            .text-main {
                fill: #0F172A;
            }

            /* ===== DARK MODE ===== */
            .dark .icon-primary {
                stroke: #60A5FA;
            }

            .dark .icon-accent {
                stroke: #34D399;
            }

            .dark .text-main {
                fill: #F8FAFC;
            }

            .font {
                font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
            }
        </style>
    </defs>

    <!-- ========= ICON ========= -->
    <g transform="translate(50,30)">
        <!-- Segmented outer indicator -->
        <circle cx="60" cy="60" r="46"
            fill="none"
            class="icon-primary"
            stroke-width="10"
            stroke-dasharray="150 80"
            stroke-linecap="round"
            transform="rotate(-40 60 60)" />

        <!-- Inner quality ring -->
        <circle cx="60" cy="60" r="30"
            fill="none"
            class="icon-accent"
            stroke-width="8" />

        <!-- Validation check -->
        <path d="M47 62 L58 73 L75 52"
            fill="none"
            class="icon-accent"
            stroke-width="6"
            stroke-linecap="round"
            stroke-linejoin="round" />
    </g>

    <!-- ========= TEXT ========= -->
    <g transform="translate(100,180)">
        <text x="0" y="0"
            font-size="36"
            font-weight="700"
            letter-spacing="-0.4"
            text-anchor="middle"
            class="text-main font">
            SI-IMUT
        </text>
    </g>

</svg>