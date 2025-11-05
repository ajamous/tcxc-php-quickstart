<?php

declare(strict_types=1);

namespace TCXC\Tests\Unit\Config;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TCXC\Config\Configuration;

class ConfigurationTest extends TestCase
{
    public function testCanCreateConfiguration(): void
    {
        $config = new Configuration([
            'host' => 'https://api.example.com',
            'login' => 'test_user',
            'api_key' => 'test_key',
            'account_id' => 123,
        ]);

        $this->assertSame('https://api.example.com', $config->getHost());
        $this->assertSame('test_user', $config->getLogin());
        $this->assertSame('test_key', $config->getApiKey());
        $this->assertSame(123, $config->getAccountId());
    }

    public function testStripsTrailingSlashFromHost(): void
    {
        $config = new Configuration([
            'host' => 'https://api.example.com/',
            'login' => 'test_user',
            'api_key' => 'test_key',
        ]);

        $this->assertSame('https://api.example.com', $config->getHost());
    }

    public function testUsesDefaultValues(): void
    {
        $config = new Configuration([
            'host' => 'https://api.example.com',
            'login' => 'test_user',
            'api_key' => 'test_key',
        ]);

        $this->assertSame(1, $config->getAccountId());
        $this->assertSame('production', $config->getEnvironment());
        $this->assertFalse($config->isDebug());
        $this->assertSame('info', $config->getLogLevel());
        $this->assertSame(30, $config->getHttpTimeout());
        $this->assertTrue($config->isSslVerifyEnabled());
    }

    public function testThrowsExceptionForMissingHost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required configuration "host" is missing or empty');

        new Configuration([
            'login' => 'test_user',
            'api_key' => 'test_key',
        ]);
    }

    public function testThrowsExceptionForMissingLogin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required configuration "login" is missing or empty');

        new Configuration([
            'host' => 'https://api.example.com',
            'api_key' => 'test_key',
        ]);
    }

    public function testThrowsExceptionForMissingApiKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required configuration "api_key" is missing or empty');

        new Configuration([
            'host' => 'https://api.example.com',
            'login' => 'test_user',
        ]);
    }

    public function testThrowsExceptionForInvalidHost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration "host" must be a valid URL');

        new Configuration([
            'host' => 'not-a-valid-url',
            'login' => 'test_user',
            'api_key' => 'test_key',
        ]);
    }

    public function testIsProductionReturnsTrueForProductionEnvironment(): void
    {
        $config = new Configuration([
            'host' => 'https://api.example.com',
            'login' => 'test_user',
            'api_key' => 'test_key',
            'environment' => 'production',
        ]);

        $this->assertTrue($config->isProduction());
        $this->assertFalse($config->isDevelopment());
    }

    public function testIsDevelopmentReturnsTrueForDevelopmentEnvironment(): void
    {
        $config = new Configuration([
            'host' => 'https://api.example.com',
            'login' => 'test_user',
            'api_key' => 'test_key',
            'environment' => 'development',
        ]);

        $this->assertTrue($config->isDevelopment());
        $this->assertFalse($config->isProduction());
    }
}
