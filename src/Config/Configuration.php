<?php

declare(strict_types=1);

namespace TCXC\Config;

use InvalidArgumentException;
use RuntimeException;

/**
 * Configuration management class for TCXC API settings.
 *
 * Provides type-safe access to configuration values loaded from environment variables.
 * Implements validation and default values for all configuration options.
 */
class Configuration
{
    private string $host;
    private string $login;
    private string $apiKey;
    private int $accountId;
    private string $environment;
    private bool $debug;
    private string $logLevel;
    private string $logPath;
    private int $httpTimeout;
    private int $httpConnectTimeout;
    private bool $sslVerify;
    private bool $rateLimitEnabled;
    private int $rateLimitMaxRequests;
    private int $rateLimitTimeWindow;

    /**
     * Create a new Configuration instance.
     *
     * @param array<string, mixed> $config Configuration array
     * @throws InvalidArgumentException If required configuration is missing
     */
    public function __construct(array $config = [])
    {
        $this->validateRequiredConfig($config);
        $this->initializeConfig($config);
    }

    /**
     * Create configuration from environment variables.
     *
     * @param string|null $envPath Path to .env file (optional)
     * @return self
     * @throws RuntimeException If .env file cannot be loaded
     */
    public static function fromEnv(?string $envPath = null): self
    {
        if ($envPath === null) {
            $envPath = dirname(__DIR__, 2);
        }

        // Load environment variables if .env file exists
        if (file_exists($envPath . '/.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable($envPath);
            $dotenv->load();
        }

        return new self([
            'host' => $_ENV['TCXC_HOST'] ?? getenv('TCXC_HOST'),
            'login' => $_ENV['TCXC_LOGIN'] ?? getenv('TCXC_LOGIN'),
            'api_key' => $_ENV['TCXC_API_KEY'] ?? getenv('TCXC_API_KEY'),
            'account_id' => (int) ($_ENV['TCXC_ACCOUNT_ID'] ?? getenv('TCXC_ACCOUNT_ID') ?: 1),
            'environment' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production',
            'debug' => filter_var(
                $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false,
                FILTER_VALIDATE_BOOLEAN
            ),
            'log_level' => $_ENV['LOG_LEVEL'] ?? getenv('LOG_LEVEL') ?: 'info',
            'log_path' => $_ENV['LOG_PATH'] ?? getenv('LOG_PATH') ?: 'logs/tcxc.log',
            'http_timeout' => (int) ($_ENV['HTTP_TIMEOUT'] ?? getenv('HTTP_TIMEOUT') ?: 30),
            'http_connect_timeout' => (int) ($_ENV['HTTP_CONNECT_TIMEOUT'] ?? getenv('HTTP_CONNECT_TIMEOUT') ?: 10),
            'ssl_verify' => filter_var(
                $_ENV['HTTP_SSL_VERIFY'] ?? getenv('HTTP_SSL_VERIFY') ?? true,
                FILTER_VALIDATE_BOOLEAN
            ),
            'rate_limit_enabled' => filter_var(
                $_ENV['RATE_LIMIT_ENABLED'] ?? getenv('RATE_LIMIT_ENABLED') ?? true,
                FILTER_VALIDATE_BOOLEAN
            ),
            'rate_limit_max_requests' => (int) ($_ENV['RATE_LIMIT_MAX_REQUESTS'] ?? getenv('RATE_LIMIT_MAX_REQUESTS') ?: 100),
            'rate_limit_time_window' => (int) ($_ENV['RATE_LIMIT_TIME_WINDOW'] ?? getenv('RATE_LIMIT_TIME_WINDOW') ?: 60),
        ]);
    }

    /**
     * Validate that required configuration values are present.
     *
     * @param array<string, mixed> $config Configuration array
     * @throws InvalidArgumentException If required configuration is missing
     */
    private function validateRequiredConfig(array $config): void
    {
        $required = ['host', 'login', 'api_key'];

        foreach ($required as $key) {
            if (empty($config[$key])) {
                throw new InvalidArgumentException(
                    sprintf('Required configuration "%s" is missing or empty', $key)
                );
            }
        }

        // Validate host is a valid URL
        if (!filter_var($config['host'], FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Configuration "host" must be a valid URL');
        }
    }

    /**
     * Initialize configuration properties from array.
     *
     * @param array<string, mixed> $config Configuration array
     */
    private function initializeConfig(array $config): void
    {
        $this->host = rtrim($config['host'], '/');
        $this->login = $config['login'];
        $this->apiKey = $config['api_key'];
        $this->accountId = $config['account_id'] ?? 1;
        $this->environment = $config['environment'] ?? 'production';
        $this->debug = $config['debug'] ?? false;
        $this->logLevel = $config['log_level'] ?? 'info';
        $this->logPath = $config['log_path'] ?? 'logs/tcxc.log';
        $this->httpTimeout = $config['http_timeout'] ?? 30;
        $this->httpConnectTimeout = $config['http_connect_timeout'] ?? 10;
        $this->sslVerify = $config['ssl_verify'] ?? true;
        $this->rateLimitEnabled = $config['rate_limit_enabled'] ?? true;
        $this->rateLimitMaxRequests = $config['rate_limit_max_requests'] ?? 100;
        $this->rateLimitTimeWindow = $config['rate_limit_time_window'] ?? 60;
    }

    // Getters

    public function getHost(): string
    {
        return $this->host;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }

    public function getHttpTimeout(): int
    {
        return $this->httpTimeout;
    }

    public function getHttpConnectTimeout(): int
    {
        return $this->httpConnectTimeout;
    }

    public function isSslVerifyEnabled(): bool
    {
        return $this->sslVerify;
    }

    public function isRateLimitEnabled(): bool
    {
        return $this->rateLimitEnabled;
    }

    public function getRateLimitMaxRequests(): int
    {
        return $this->rateLimitMaxRequests;
    }

    public function getRateLimitTimeWindow(): int
    {
        return $this->rateLimitTimeWindow;
    }

    /**
     * Check if running in production environment.
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /**
     * Check if running in development environment.
     *
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }
}
