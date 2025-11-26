<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    /** @test */
    public function home_page_loads_ok()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
