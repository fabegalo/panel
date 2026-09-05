<?php

namespace Pterodactyl\Tests\Unit\Transformers\Api\Client;

use Pterodactyl\Tests\TestCase;
use Pterodactyl\Models\Database;
use Pterodactyl\Models\DatabaseHost;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Contracts\Extensions\HashidsInterface;
use Pterodactyl\Transformers\Api\Client\DatabaseTransformer;

class DatabaseTransformerTest extends TestCase
{
    public function testCustomerFacingEndpointCanDifferFromInternalDatabaseHost(): void
    {
        config()->set('pterodactyl.client_features.databases.display_host', 'database.waybercraft.com.br');
        config()->set('pterodactyl.client_features.databases.display_port', 3306);

        $hashids = \Mockery::mock(HashidsInterface::class);
        $hashids->expects('encode')->with(42)->andReturn('database-id');

        $transformer = new DatabaseTransformer();
        $transformer->handle(\Mockery::mock(Encrypter::class), $hashids);

        $database = Database::factory()->make([
            'id' => 42,
            'database' => 's4_minigames',
            'username' => 'u4_customer',
            'remote' => '%',
        ]);
        $database->setRelation('host', DatabaseHost::factory()->make([
            'host' => 'customer-database',
            'port' => 3306,
        ]));

        $result = $transformer->transform($database);

        $this->assertSame([
            'address' => 'database.waybercraft.com.br',
            'port' => 3306,
        ], $result['host']);
    }
}
