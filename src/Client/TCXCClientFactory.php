<?php

declare(strict_types=1);

namespace TCXC\Client;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use TCXC\Config\Configuration;

/**
 * Factory for creating TCXCClient instances.
 *
 * Provides convenient methods for creating clients with different
 * configurations and logging setups.
 */
class TCXCClientFactory
{
    /**
     * Create a client from environment variables.
     *
     * @param string|null $envPath Path to .env file (optional)
     * @param LoggerInterface|null $logger Optional logger instance
     * @return TCXCClient
     */
    public static function createFromEnv(?string $envPath = null, ?LoggerInterface $logger = null): TCXCClient
    {
        $config = Configuration::fromEnv($envPath);

        if ($logger === null && !$config->isProduction()) {
            $logger = self::createDefaultLogger($config);
        }

        return new TCXCClient($config, $logger);
    }

    /**
     * Create a client with custom configuration.
     *
     * @param array<string, mixed> $config Configuration array
     * @param LoggerInterface|null $logger Optional logger instance
     * @return TCXCClient
     */
    public static function create(array $config, ?LoggerInterface $logger = null): TCXCClient
    {
        $configuration = new Configuration($config);

        if ($logger === null && !$configuration->isProduction()) {
            $logger = self::createDefaultLogger($configuration);
        }

        return new TCXCClient($configuration, $logger);
    }

    /**
     * Create a default logger instance based on configuration.
     *
     * @param Configuration $config Configuration
     * @return LoggerInterface
     */
    private static function createDefaultLogger(Configuration $config): LoggerInterface
    {
        $logger = new Logger('tcxc');

        // Ensure log directory exists
        $logPath = $config->getLogPath();
        $logDir = dirname($logPath);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Map log level string to Monolog level
        $levelMap = [
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'notice' => Logger::NOTICE,
            'warning' => Logger::WARNING,
            'error' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
            'alert' => Logger::ALERT,
            'emergency' => Logger::EMERGENCY,
        ];

        $level = $levelMap[$config->getLogLevel()] ?? Logger::INFO;

        $logger->pushHandler(new StreamHandler($logPath, $level));

        return $logger;
    }
}
