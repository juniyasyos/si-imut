import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',
        './vendor/diogogpinto/filament-auth-ui-enhancer/resources/**/*.blade.php',
        './vendor/guava/filament-modal-relation-managers/resources/**/*.blade.php',
        './vendor/bezhansalleh/filament-language-switch/resources/**/*.blade.php',
        './vendor/bezhansalleh/filament-shield/resources/**/*.blade.php',
        './vendor/stechstudio/filament-impersonate/resources/**/*.blade.php',
        './vendor/pxlrbt/filament-excel/resources/**/*.blade.php',
        './vendor/leandrocfe/filament-apex-charts/resources/**/*.blade.php',
        './vendor/rmsramos/activitylog/resources/**/*.blade.php',
    ],
}
