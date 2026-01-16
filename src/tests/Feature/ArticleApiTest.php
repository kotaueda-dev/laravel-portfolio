<?php

test('サンプルテスト', function () {
    $response = $this->get('/');

    $response->assertStatus(404);
});
