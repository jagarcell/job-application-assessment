<?php

it('home page loads ok', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});
