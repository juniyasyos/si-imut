@props(['size' => '48'])

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 140 140" width="{{ $size }}" height="{{ $size }}"
    role="img" aria-label="SI-IMUT Icon" {{ $attributes }}>
    <defs>
        <style>
            .logo-icon-primary {
                stroke: #0F4CAD;
            }

            .logo-icon-accent {
                stroke: #00A78E;
            }

            .logo-icon-muted {
                stroke: #94A3B8;
            }

            /* Dark mode styles */
            .dark .logo-icon-primary {
                stroke: #60A5FA;
            }

            .dark .logo-icon-accent {
                stroke: #34D399;
            }

            .dark .logo-icon-muted {
                stroke: #334155;
            }
        </style>
    </defs>

    <!-- Basis ring tipis -->
    <circle cx="70" cy="70" r="46" fill="none" class="logo-icon-muted" stroke-width="12" opacity="0.5" />

    <!-- Arc biru (progress utama) -->
    <g transform="rotate(-35 70 70)">
        <circle cx="70" cy="70" r="46" fill="none" class="logo-icon-primary" stroke-width="12"
            stroke-linecap="round" stroke-dasharray="180 400" />
    </g>

    <!-- Arc hijau kecil (komponen/segment lain) -->
    <g transform="rotate(135 70 70)">
        <circle cx="70" cy="70" r="46" fill="none" class="logo-icon-accent" stroke-width="12"
            stroke-linecap="round" stroke-dasharray="70 400" />
    </g>

    <!-- Checkmark di tengah -->
    <path d="M52 70 l14 14 28-30" fill="none" class="logo-icon-accent" stroke-width="12" stroke-linecap="round"
        stroke-linejoin="round" />
</svg>
