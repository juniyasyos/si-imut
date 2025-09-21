<?php

test('php works', function () {
    expect(true)->toBe(true);
    expect(1 + 1)->toBe(2);
    expect('hello world')->toContain('world');
});

test('array operations', function () {
    $arr = [1, 2, 3];
    expect($arr)->toHaveCount(3);
    expect($arr[0])->toBe(1);
});
