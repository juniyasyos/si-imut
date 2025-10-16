<?php

it('domain layer no longer references legacy service namespace', function () {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path('Domains')));

    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
            $contents = file_get_contents($file->getPathname());
            expect($contents)->not->toContain('use App\\Services\\');
        }
    }
});
