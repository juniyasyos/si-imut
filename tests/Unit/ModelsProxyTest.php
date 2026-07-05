<?php

use Tests\TestCase;

uses(TestCase::class);

test('all proxy models in app/Models can be instantiated', function () {
    $models = glob(app_path('Models/*.php'));
    
    expect($models)->not->toBeEmpty();

    foreach ($models as $modelFile) {
        $className = 'App\\Models\\' . basename($modelFile, '.php');
        
        // This will trigger autoloading and fail if there's a missing parent class
        expect(class_exists($className))->toBeTrue();
    }
});
