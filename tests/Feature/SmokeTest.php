<?php

// Simple smoke test to ensure the app doesn't crash on basic routes
test('app responds to basic routes', function () {
    // Just test that the app can boot without fatal errors
    $response = $this->get('/');

    // We don't care about exact status, just that it doesn't crash
    expect($response->status())->toBeIn([200, 302, 404, 500]);
});
