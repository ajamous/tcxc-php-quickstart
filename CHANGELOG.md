# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-01-XX - Enterprise Edition

### Added

#### Architecture
- Complete refactor to enterprise-grade object-oriented architecture
- PSR-4 autoloading with proper namespace structure (`TCXC\`)
- PSR-3 logging support with Monolog integration
- Full dependency injection throughout the codebase
- Builder pattern for fluent API (CallRequestBuilder)
- Factory pattern for client creation (TCXCClientFactory)

#### Configuration Management
- Environment-based configuration with `.env` support via vlucas/phpdotenv
- `Configuration` class with type-safe getters
- `CarrierRegistry` for managing carrier mappings
- Support for custom carrier definitions
- Comprehensive validation of configuration values

#### Models
- `CallLeg` model for representing call participants
- `CallRequest` model for bridge call requests
- `CallResponse` model for API responses
- Full input validation with detailed error messages
- E.164 phone number format validation
- Array serialization/deserialization support

#### Exception Handling
- `TCXCException` base exception class
- `ConfigurationException` for configuration errors
- `ValidationException` with field-level error details
- `AuthenticationException` for auth failures
- `ApiException` for HTTP/API errors
- `RateLimitException` with retry-after support

#### HTTP Client
- Modern Guzzle-based HTTP client
- Proper SSL certificate verification (enabled by default)
- Configurable timeouts and connection settings
- HMAC-SHA256 signature authentication
- Comprehensive error handling and status code mapping
- User-Agent header for SDK identification

#### Security
- SSL verification enabled by default in production
- Environment variables for sensitive credentials
- No credentials in source code
- Input validation and sanitization
- Rate limiting awareness

#### Testing
- PHPUnit 9.6 test suite
- Comprehensive unit tests for all major components
- Test coverage for Configuration, CarrierRegistry, CallLeg
- PHPUnit XML configuration
- Support for code coverage reports

#### Code Quality Tools
- PHPStan static analysis (level 8)
- PHP-CS-Fixer for PSR-12 code standards
- Composer scripts for quality checks
- Pre-commit hook support

#### Documentation
- Comprehensive README.md with examples
- CONTRIBUTING.md with contribution guidelines
- PHPDoc comments on all classes and methods
- Usage examples in `public/` directory
- Docker setup documentation
- API reference documentation

#### Developer Experience
- Composer scripts for common tasks (`test`, `analyse`, `cs:fix`, `quality`)
- Example files demonstrating various usage patterns
- Detailed error messages with actionable information
- Type hints throughout for better IDE support

#### DevOps
- Dockerfile for production deployment
- Dockerfile.dev for development environment
- docker-compose.yml for easy setup
- .dockerignore for optimized builds
- .gitignore for clean repository

#### Logging
- PSR-3 compliant logging interface
- Monolog integration with file handler
- Configurable log levels (debug, info, warning, error)
- Structured logging with context
- Request/response logging for debugging

### Changed

- **Breaking**: Complete API redesign - not backward compatible with v1.x
- Moved from procedural to object-oriented paradigm
- Changed from global variables to configuration objects
- Replaced direct cURL usage with Guzzle HTTP client
- Enhanced error handling from simple errors to typed exceptions
- Improved phone number validation with E.164 format support

### Deprecated

- Legacy procedural files (`auth.php`, `carrier.php`, `MakeCall.php`)
  - These are preserved for reference but not used in v2.0
  - Will be removed in future versions

### Removed

- Direct use of global variables
- Hardcoded credentials in source files
- SSL verification bypass (now properly enabled)
- Magic numbers (replaced with constants and registry)

### Fixed

- SSL certificate verification now enabled by default
- Proper error handling for all API failures
- Input validation prevents invalid requests
- Phone number normalization handles various formats
- Type safety prevents runtime type errors

### Security

- **CRITICAL**: SSL verification now enabled by default (was disabled in v1.x)
- Credentials moved from source code to environment variables
- Added comprehensive input validation
- HMAC-SHA256 signature for request authentication
- Rate limiting awareness and proper handling

## [1.0.0] - 2023-XX-XX - Initial Release

### Added

- Basic procedural implementation for TCXC Voice API
- `auth.php` for credentials configuration
- `carrier.php` for carrier ID mappings
- `MakeCall.php` for initiating bridge calls
- Basic cURL HTTP client
- HMAC-SHA256 request signing
- README.md with basic usage instructions

### Known Issues (v1.x)

- SSL verification disabled (security risk)
- Credentials in source code (security risk)
- No error handling
- No input validation
- Procedural code (not object-oriented)
- No tests or code quality tools
- No logging or debugging support

---

## Migration Guide: v1.x to v2.0

### Before (v1.x)

```php
<?php
require_once 'auth.php';
require_once 'carrier.php';

$cld1 = '19542405555';
$cli1 = '1800999999';
$i_connection1 = $TATA;

$cld2 = '18667471117';
$cli2 = '12345678988';
$i_connection2 = $VIBER;

// ... cURL code
```

### After (v2.0)

```php
<?php
require_once 'vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;

$client = TCXCClientFactory::createFromEnv();
$carriers = new CarrierRegistry();

$request = CallRequest::builder()
    ->setLegADestination('19542405555')
    ->setLegACallerId('18009999999')
    ->setLegAConnectionId($carriers->getCarrierId('TATA'))
    ->setLegBDestination('18667471117')
    ->setLegBCallerId('12345678988')
    ->setLegBConnectionId($carriers->getCarrierId('VIBER'))
    ->build();

$response = $client->initiateCall($request);
```

### Key Changes

1. **Configuration**: Move credentials from `auth.php` to `.env` file
2. **Autoloading**: Use Composer autoloader instead of `require_once`
3. **Namespaces**: All classes are under `TCXC\` namespace
4. **Error Handling**: Wrap calls in try-catch blocks
5. **Type Safety**: Use type-hinted objects instead of primitive variables
6. **Validation**: Input is now automatically validated
7. **Security**: SSL verification enabled - ensure your environment supports it

---

[2.0.0]: https://github.com/yourusername/tcxc-php-quickstart/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/yourusername/tcxc-php-quickstart/releases/tag/v1.0.0
