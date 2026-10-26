<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * The homepage redirects to the existing catalogue route.
     *
     * @return void
     */
    public function testHomepageRedirectsToCatalogue()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/censored?');
    }
}
