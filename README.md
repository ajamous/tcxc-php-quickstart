# TCXC PHP SDK - Enterprise Edition

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Enterprise-grade PHP SDK for TelecomsXChange (TCXC) Voice API. Make outbound voice calls via PHP with professional-grade architecture, comprehensive error handling, and best practices.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Usage Examples](#usage-examples)
- [Architecture](#architecture)
- [Testing](#testing)
- [Code Quality](#code-quality)
- [Docker Support](#docker-support)
- [API Reference](#api-reference)
- [Error Handling](#error-handling)
- [Contributing](#contributing)
- [License](#license)

## Features

### Enterprise-Grade Architecture
- **Object-Oriented Design**: Full OOP implementation with SOLID principles
- **PSR-4 Autoloading**: Modern PHP autoloading standards
- **PSR-3 Logging**: Compatible with any PSR-3 logger (Monolog included)
- **Dependency Injection**: Flexible and testable architecture
- **Type Safety**: Full PHP type hints and strict types

### Security & Best Practices
- **SSL Verification**: Enabled by default for all API calls
- **Environment Variables**: Secure configuration management with `.env` support
- **Input Validation**: Comprehensive validation for all inputs
- **Rate Limiting**: Built-in rate limit handling
- **Error Handling**: Custom exceptions for different error scenarios

### Developer Experience
- **Fluent Builder Pattern**: Intuitive API for building call requests
- **Comprehensive Testing**: PHPUnit tests with 80%+ coverage
- **Static Analysis**: PHPStan level 8 compliance
- **Code Standards**: PSR-12 coding standards with PHP-CS-Fixer
- **Docker Support**: Ready-to-use containerization
- **Detailed Logging**: Debug and troubleshoot with ease

### TCXC Voice API Benefits
- Empower voice communications in web forms, mobile apps, and websites
- Choose carriers for optimal call quality and pricing
- Switch between carriers easily
- Integrate with custom CRM, Salesforce, or Helpdesk
- Wholesale pricing

## Requirements

- PHP 7.4 or higher
- Composer
- PHP extensions:
  - `curl`
  - `json`
  - `mbstring`
- TCXC API credentials ([Get API Key](https://www.telecomsxchange.com))

## Installation

### Using Composer

```bash
composer require tcxc/php-quickstart
```

### From Source

```bash
git clone https://github.com/yourusername/tcxc-php-quickstart.git
cd tcxc-php-quickstart
composer install
```

### Environment Setup

1. Copy the example environment file:
```bash
cp .env.example .env
```

2. Edit `.env` and add your TCXC credentials:
```env
TCXC_HOST=https://members.telecomsxchange.com
TCXC_LOGIN=your_buyer_username
TCXC_API_KEY=your_api_key
TCXC_ACCOUNT_ID=1
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;

// Create client from environment variables
$client = TCXCClientFactory::createFromEnv();

// Get carrier registry
$carriers = new CarrierRegistry();

// Build and initiate call
$callRequest = CallRequest::builder()
    ->setLegADestination('19542405555')
    ->setLegACallerId('18009999999')
    ->setLegAConnectionId($carriers->getCarrierId('TATA'))
    ->setLegBDestination('18667471117')
    ->setLegBCallerId('12345678988')
    ->setLegBConnectionId($carriers->getCarrierId('VIBER'))
    ->build();

$response = $client->initiateCall($callRequest);

if ($response->isSuccess()) {
    echo "Call initiated! ID: " . $response->getCallId();
}
```

## Configuration

### Environment Variables

All configuration can be managed via environment variables:

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `TCXC_HOST` | TCXC API host URL | - | Yes |
| `TCXC_LOGIN` | Your buyer username | - | Yes |
| `TCXC_API_KEY` | Your API key | - | Yes |
| `TCXC_ACCOUNT_ID` | Account ID for billing | `1` | No |
| `APP_ENV` | Environment (production/development) | `production` | No |
| `APP_DEBUG` | Enable debug mode | `false` | No |
| `LOG_LEVEL` | Log level (debug/info/error) | `info` | No |
| `LOG_PATH` | Path to log file | `logs/tcxc.log` | No |
| `HTTP_TIMEOUT` | HTTP request timeout (seconds) | `30` | No |
| `HTTP_CONNECT_TIMEOUT` | HTTP connect timeout (seconds) | `10` | No |
| `HTTP_SSL_VERIFY` | Verify SSL certificates | `true` | No |

### Manual Configuration

```php
use TCXC\Client\TCXCClient;
use TCXC\Config\Configuration;

$config = new Configuration([
    'host' => 'https://members.telecomsxchange.com',
    'login' => 'your_username',
    'api_key' => 'your_api_key',
    'account_id' => 1,
    'ssl_verify' => true,
]);

$client = new TCXCClient($config);
```

### Carrier Configuration

```php
use TCXC\Config\CarrierRegistry;

// Use default carriers (TATA, VIBER, IBASIS)
$carriers = new CarrierRegistry();

// Add custom carriers
$carriers->addCarrier('MY_CARRIER', 12345);

// Or create with custom carriers
$carriers = new CarrierRegistry([
    'CUSTOM_CARRIER_1' => 100,
    'CUSTOM_CARRIER_2' => 200,
]);

// Get carrier ID
$carrierId = $carriers->getCarrierId('TATA'); // Returns 220
```

## Usage Examples

### Example 1: Basic Call

```php
use TCXC\Client\TCXCClientFactory;
use TCXC\Model\CallLeg;
use TCXC\Model\CallRequest;

$client = TCXCClientFactory::createFromEnv();

$legA = new CallLeg('19542405555', '18009999999', 220);
$legB = new CallLeg('18667471117', '12345678988', 1771);

$request = new CallRequest($legA, $legB);
$response = $client->initiateCall($request);
```

### Example 2: Using Builder Pattern

```php
$callRequest = CallRequest::builder()
    ->setLegADestination('19542405555')
    ->setLegACallerId('18009999999')
    ->setLegAConnectionId(220)
    ->setLegBDestination('18667471117')
    ->setLegBCallerId('12345678988')
    ->setLegBConnectionId(1771)
    ->setMetadata(['campaign' => 'sales', 'user_id' => '123'])
    ->build();
```

### Example 3: Error Handling

```php
use TCXC\Exception\ApiException;
use TCXC\Exception\AuthenticationException;
use TCXC\Exception\ValidationException;
use TCXC\Exception\RateLimitException;

try {
    $response = $client->initiateCall($callRequest);
} catch (ValidationException $e) {
    // Handle validation errors
    foreach ($e->getErrors() as $field => $errors) {
        echo "$field: " . implode(', ', $errors) . "\n";
    }
} catch (AuthenticationException $e) {
    // Handle authentication errors
    echo "Auth failed: " . $e->getMessage();
} catch (RateLimitException $e) {
    // Handle rate limiting
    echo "Rate limited. Retry after: " . $e->getRetryAfter() . " seconds";
} catch (ApiException $e) {
    // Handle general API errors
    echo "API error: " . $e->getMessage();
    echo "Status code: " . $e->getHttpStatusCode();
}
```

### Example 4: Custom Logging

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('my-app');
$logger->pushHandler(new StreamHandler('my-app.log', Logger::DEBUG));

$client = new TCXCClient($config, $logger);
```

## Architecture

### Project Structure

```
tcxc-php-quickstart/
├── src/
│   ├── Client/              # API client implementation
│   │   ├── TCXCClient.php
│   │   └── TCXCClientFactory.php
│   ├── Config/              # Configuration management
│   │   ├── Configuration.php
│   │   └── CarrierRegistry.php
│   ├── Exception/           # Custom exceptions
│   │   ├── TCXCException.php
│   │   ├── ApiException.php
│   │   ├── AuthenticationException.php
│   │   ├── ValidationException.php
│   │   └── RateLimitException.php
│   └── Model/               # Data models
│       ├── CallLeg.php
│       ├── CallRequest.php
│       └── CallResponse.php
├── tests/
│   ├── Unit/                # Unit tests
│   └── Integration/         # Integration tests
├── public/                  # Example files
├── config/                  # Configuration files
└── logs/                    # Log files
```

### Design Patterns

- **Factory Pattern**: `TCXCClientFactory` for client creation
- **Builder Pattern**: `CallRequestBuilder` for fluent API
- **Repository Pattern**: `CarrierRegistry` for carrier management
- **Dependency Injection**: Constructor injection throughout
- **Exception Hierarchy**: Custom exceptions for better error handling

## Testing

### Run All Tests

```bash
composer test
```

### Run with Coverage

```bash
composer test:coverage
```

### Run Specific Test Suite

```bash
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Integration
```

## Code Quality

### Static Analysis

```bash
composer analyse
```

### Code Style Check

```bash
composer cs:check
```

### Code Style Fix

```bash
composer cs:fix
```

### Run All Quality Checks

```bash
composer quality
```

## Docker Support

### Build and Run

```bash
docker-compose up -d
```

### Run Commands in Container

```bash
docker-compose exec php composer test
docker-compose exec php composer analyse
```

## API Reference

### TCXCClient

#### `initiateCall(CallRequest $request): CallResponse`

Initiates a bridge call between two phone numbers.

**Parameters:**
- `$request` - CallRequest object containing call details

**Returns:** CallResponse object

**Throws:**
- `ValidationException` - If request validation fails
- `AuthenticationException` - If authentication fails
- `RateLimitException` - If rate limit is exceeded
- `ApiException` - If API request fails

### CallRequest

#### Builder Methods

- `setLegADestination(string $destination): self`
- `setLegACallerId(string $callerId): self`
- `setLegAConnectionId(int $connectionId): self`
- `setLegBDestination(string $destination): self`
- `setLegBCallerId(string $callerId): self`
- `setLegBConnectionId(int $connectionId): self`
- `setMetadata(array $metadata): self`
- `build(): CallRequest`

### CarrierRegistry

#### Methods

- `getCarrierId(string $name): int` - Get carrier ID by name
- `hasCarrier(string $name): bool` - Check if carrier exists
- `addCarrier(string $name, int $id): self` - Add custom carrier
- `getAllCarriers(): array` - Get all carriers
- `getCarrierName(int $id): ?string` - Get carrier name by ID

## Error Handling

### Exception Hierarchy

```
Exception
└── TCXCException (base exception)
    ├── ConfigurationException
    ├── ValidationException
    ├── AuthenticationException
    └── ApiException
        └── RateLimitException
```

### Best Practices

1. Always catch specific exceptions before general ones
2. Use ValidationException to display user-friendly error messages
3. Log all exceptions for debugging
4. Handle RateLimitException with exponential backoff
5. Never expose API keys or credentials in error messages

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes
4. Ensure all tests pass (`composer quality`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- **Documentation**: [https://docs.telecomsxchange.com](https://docs.telecomsxchange.com)
- **API Key**: [https://www.telecomsxchange.com](https://www.telecomsxchange.com)
- **Issues**: [GitHub Issues](https://github.com/yourusername/tcxc-php-quickstart/issues)

## Changelog

### Version 2.0.0 (Enterprise Edition)

- Complete refactor to enterprise-grade architecture
- Object-oriented design with SOLID principles
- PSR-4 autoloading and PSR-3 logging
- Comprehensive error handling with custom exceptions
- Full test coverage with PHPUnit
- Static analysis with PHPStan (level 8)
- Code standards with PHP-CS-Fixer (PSR-12)
- Docker support for containerization
- Environment-based configuration
- Builder pattern for fluent API
- Detailed logging and debugging
- Security improvements (SSL verification, input validation)

### Version 1.0.0 (Legacy)

- Basic procedural implementation
- Simple API integration
- Minimal error handling

---

Made with ❤️ for developers by the TCXC team
