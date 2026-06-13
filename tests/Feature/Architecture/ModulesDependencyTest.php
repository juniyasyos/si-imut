<?php

namespace Tests\Feature\Architecture;

use Tests\TestCase;

class ModulesDependencyTest extends TestCase
{
    /** @test */
    public function modules_only_depend_on_contracts_of_other_modules()
    {
        $modulesPath = app_path('Modules');
        if (!is_dir($modulesPath)) {
            $this->assertTrue(true);
            return;
        }

        $modules = array_filter(scandir($modulesPath), function ($item) use ($modulesPath) {
            return is_dir($modulesPath . '/' . $item) && !in_array($item, ['.', '..']);
        });

        foreach ($modules as $moduleName) {
            $moduleDir = $modulesPath . '/' . $moduleName;
            $files = $this->getPhpFiles($moduleDir);

            foreach ($files as $file) {
                $content = file_get_contents($file);
                
                // Find all "use App\Modules\<OtherModule>..." imports
                preg_match_all('/use\s+App\\\Modules\\\([^\\\;]+)([^;]*);/', $content, $matches);

                if (!empty($matches[1])) {
                    foreach ($matches[1] as $idx => $referencedModule) {
                        // Skip if it's the module referencing itself
                        if ($referencedModule === $moduleName) {
                            continue;
                        }

                        $remainingNamespace = $matches[2][$idx];

                        // Allowed: imports from Contracts/ or Events/
                        $isAllowed = str_contains($remainingNamespace, '\\Contracts\\') 
                                  || str_contains($remainingNamespace, '\\Events\\')
                                  || str_contains($remainingNamespace, '\\Contracts')
                                  || str_contains($remainingNamespace, '\\Events');

                        $this->assertTrue(
                            $isAllowed,
                            "Architecture violation in file:\n{$file}\n" .
                            "Module '{$moduleName}' cannot directly reference concrete class " .
                            "'App\\Modules\\{$referencedModule}{$remainingNamespace}'.\n" .
                            "It must only reference Interfaces under Contracts or Events."
                        );
                    }
                }
            }
        }
        $this->assertTrue(true);
    }

    private function getPhpFiles(string $dir): array
    {
        $files = [];
        $items = scandir($dir);

        foreach ($items as $item) {
            if (in_array($item, ['.', '..'])) {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $files = array_merge($files, $this->getPhpFiles($path));
            } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $files[] = $path;
            }
        }

        return $files;
    }
}
