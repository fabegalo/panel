<?php

namespace Pterodactyl\Tests\Integration\Api\Application\Nodes;

use Pterodactyl\Models\Node;
use Pterodactyl\Models\Location;
use Pterodactyl\Services\Acl\Api\AdminAcl;
use Pterodactyl\Tests\Integration\Api\Application\ApplicationApiIntegrationTestCase;

class NodeConfigurationControllerTest extends ApplicationApiIntegrationTestCase
{
    public function testWriteNodeKeyCanGetNodeConfiguration(): void
    {
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_nodes' => AdminAcl::READ | AdminAcl::WRITE]);

        $node = Node::factory()->for(Location::factory())->create();

        $response = $this->getJson('/api/application/nodes/' . $node->id . '/configuration');

        $response->assertOk();
        $response->assertJsonPath('uuid', $node->uuid);
        $response->assertJsonPath('token_id', $node->daemon_token_id);
        $this->assertSame(decrypt($node->daemon_token), $response->json('token'));
    }

    public function testPublicSftpEndpointDoesNotChangeWingsConfiguration(): void
    {
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_nodes' => AdminAcl::READ | AdminAcl::WRITE]);

        $node = Node::factory()->for(Location::factory())->create([
            'daemonSFTP' => 2022,
            'public_sftp_host' => 'sftp.example.com',
            'public_sftp_port' => 2202,
        ]);

        $this->getJson('/api/application/nodes/' . $node->id . '/configuration')
            ->assertOk()
            ->assertJsonPath('system.sftp.bind_port', 2022)
            ->assertJsonMissingPath('public_sftp_host')
            ->assertJsonMissingPath('public_sftp_port');
    }

    public function testReadOnlyNodeKeyCannotGetNodeConfiguration(): void
    {
        $this->createNewDefaultApiKey($this->getApiUser(), ['r_nodes' => AdminAcl::READ]);

        $node = Node::factory()->for(Location::factory())->create();

        $this->assertAccessDeniedJson($this->getJson('/api/application/nodes/' . $node->id . '/configuration'));
    }
}
