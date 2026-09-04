<?php

namespace Pterodactyl\Tests\Unit\Rules;

use Pterodactyl\Rules\Hostname;
use Pterodactyl\Tests\TestCase;

class HostnameTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('validHostnameDataProvider')]
    public function testValidHostnames(string $hostname): void
    {
        $this->assertTrue((new Hostname())->passes('host', $hostname));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidHostnameDataProvider')]
    public function testInvalidHostnames(string $hostname): void
    {
        $this->assertFalse((new Hostname())->passes('host', $hostname));
    }

    public static function validHostnameDataProvider(): array
    {
        return [
            ['sftp.example.com'],
            ['edge-001.example.com'],
            ['137.131.135.236'],
            ['2001:db8::1'],
        ];
    }

    public static function invalidHostnameDataProvider(): array
    {
        return [
            ['https://sftp.example.com'],
            ['sftp.example.com:2022'],
            ['invalid host.example.com'],
            ['-invalid.example.com'],
        ];
    }
}
