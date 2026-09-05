<?php

namespace Pterodactyl\Tests\Integration\Http\Controllers;

use Pterodactyl\Tests\Integration\IntegrationTestCase;

class CsrfTokenControllerTest extends IntegrationTestCase
{
    public function testReturnsTokenFromCurrentSessionWithoutCaching(): void
    {
        $response = $this->withSession(['csrf-test' => true])->getJson('/csrf-token');

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonStructure(['token']);

        $this->assertSame(session()->token(), $response->json('token'));
    }
}
