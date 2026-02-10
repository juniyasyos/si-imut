@props(['width' => '200'])

<svg xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 720 180"
    width="{{ $width }}"
    height="{{ $width * 0.25 }}"
    role="img"
    aria-labelledby="logo-title logo-desc"
    {{ $attributes }}>

    <title id="logo-title">SI-IMUT — Sistem Informasi Indikator Mutu Terintegrasi</title>
    <desc id="logo-desc">Ikon indikator mutu terintegrasi.</desc>

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
    <g transform="translate(30,30)">
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
    <g transform="translate(200,55)">
        <text x="0" y="48"
            font-size="64"
            font-weight="700"
            letter-spacing="-0.6"
            class="text-main font">
            SI-IMUT
        </text>

        <text x="0" y="88"
            font-size="26"
            font-weight="400"
            class="text-main font"
            opacity="0.85">
            Sistem Informasi Indikator Mutu Terintegrasi
        </text>
    </g>

</svg>