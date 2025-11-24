<?php

test('api health endpoint returns ok status', function () {
    $response = $this->getJson('/api/health');

    $response
        ->assertStatus(200)
        ->assertJson([
            'status' => 'ok',
            'service' => 'Red Lane API',
            'version' => '1.0.0',
        ])
        ->assertJsonStructure([
            'status',
            'timestamp',
            'service',
            'version',
        ]);
});
