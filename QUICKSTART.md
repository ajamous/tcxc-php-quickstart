# TCXC PHP SDK - Quick Start Guide

Get started with the TCXC PHP SDK in minutes! This guide shows you the most common use cases.

## Table of Contents
1. [Basic Setup](#basic-setup)
2. [Example 1: Simple Call](#example-1-simple-call)
3. [Example 2: Using Builder Pattern](#example-2-using-builder-pattern)
4. [Example 3: Custom Configuration](#example-3-custom-configuration)
5. [Example 4: Error Handling](#example-4-error-handling)
6. [Example 5: Custom Carriers](#example-5-custom-carriers)
7. [Example 6: With Logging](#example-6-with-logging)

---

## Basic Setup

### 1. Install Dependencies
```bash
composer require tcxc/php-quickstart
# or for development
git clone https://github.com/yourusername/tcxc-php-quickstart.git
cd tcxc-php-quickstart
composer install
```

### 2. Configure Environment
```bash
cp .env.example .env
```

Edit `.env` with your credentials:
```env
TCXC_HOST=https://members.telecomsxchange.com
TCXC_LOGIN=your_buyer_username
TCXC_API_KEY=your_api_key_here
TCXC_ACCOUNT_ID=1
```

---

## Example 1: Simple Call

**Scenario**: Make a quick call with minimal code

```php
<?php

require_once 'vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallLeg;
use TCXC\Model\CallRequest;

// Create client from .env file
$client = TCXCClientFactory::createFromEnv();

// Get carriers
$carriers = new CarrierRegistry();

// Create call legs
$legA = new CallLeg(
    '19542405555',                      // Destination number
    '18009999999',                      // Caller ID to display
    $carriers->getCarrierId('TATA')     // Carrier
);

$legB = new CallLeg(
    '18667471117',
    '12345678988',
    $carriers->getCarrierId('VIBER')
);

// Make the call
$request = new CallRequest($legA, $legB);
$response = $client->initiateCall($request);

// Check result
if ($response->isSuccess()) {
    echo "✓ Call initiated!\n";
    echo "Call ID: " . $response->getCallId() . "\n";
} else {
    echo "✗ Call failed: " . $response->getMessage() . "\n";
}
```

**Output:**
```
✓ Call initiated!
Call ID: abc123xyz
```

---

## Example 2: Using Builder Pattern

**Scenario**: Fluent API for readable code

```php
<?php

require_once 'vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;

$client = TCXCClientFactory::createFromEnv();
$carriers = new CarrierRegistry();

// Build request with fluent interface
$request = CallRequest::builder()
    ->setLegADestination('19542405555')
    ->setLegACallerId('18009999999')
    ->setLegAConnectionId($carriers->getCarrierId('TATA'))
    ->setLegBDestination('18667471117')
    ->setLegBCallerId('12345678988')
    ->setLegBConnectionId($carriers->getCarrierId('VIBER'))
    ->setMetadata([
        'campaign' => 'sales_outbound',
        'agent_id' => '42',
        'customer_id' => '12345'
    ])
    ->build();

$response = $client->initiateCall($request);

echo $response->toJson();
```

**Output:**
```json
{
    "success": true,
    "call_id": "abc123xyz",
    "message": "Call initiated successfully",
    "raw_response": { ... }
}
```

---

## Example 3: Custom Configuration

**Scenario**: Manual configuration without .env file

```php
<?php

require_once 'vendor/autoload.php';

use TCXC\Client\TCXCClient;
use TCXC\Config\Configuration;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;

// Manual configuration
$config = new Configuration([
    'host' => 'https://members.telecomsxchange.com',
    'login' => 'your_username',
    'api_key' => 'your_api_key',
    'account_id' => 1,
    'environment' => 'production',
    'debug' => false,
    'http_timeout' => 30,
    'ssl_verify' => true,  // Always true in production!
]);

// Create client
$client = new TCXCClient($config);

// Use as normal
$carriers = new CarrierRegistry();
$request = CallRequest::builder()
    ->setLegADestination('19542405555')
    ->setLegACallerId('18009999999')
    ->setLegAConnectionId(220)  // Direct carrier ID
    ->setLegBDestination('18667471117')
    ->setLegBCallerId('12345678988')
    ->setLegBConnectionId(1771)
    ->build();

$response = $client->initiateCall($request);
```

---

## Example 4: Error Handling

**Scenario**: Comprehensive error handling for production

```php
<?php

require_once 'vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;
use TCXC\Exception\ValidationException;
use TCXC\Exception\AuthenticationException;
use TCXC\Exception\RateLimitException;
use TCXC\Exception\ApiException;
use TCXC\Exception\TCXCException;

try {
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

    if ($response->isSuccess()) {
        echo "Success! Call ID: " . $response->getCallId() . "\n";
    } else {
        echo "Failed: " . $response->getMessage() . "\n";
    }

} catch (ValidationException $e) {
    // Handle validation errors (invalid phone numbers, missing fields)
    echo "Validation Error: " . $e->getMessage() . "\n";

    foreach ($e->getErrors() as $field => $errors) {
        echo "  - $field: " . implode(', ', $errors) . "\n";
    }

} catch (AuthenticationException $e) {
    // Handle authentication errors (invalid credentials)
    echo "Authentication Failed: " . $e->getMessage() . "\n";
    echo "Please check your TCXC_LOGIN and TCXC_API_KEY\n";

} catch (RateLimitException $e) {
    // Handle rate limiting
    echo "Rate Limit Exceeded: " . $e->getMessage() . "\n";

    if ($retryAfter = $e->getRetryAfter()) {
        echo "Retry after $retryAfter seconds\n";
        // Implement exponential backoff here
    }

} catch (ApiException $e) {
    // Handle API errors
    echo "API Error: " . $e->getMessage() . "\n";
    echo "HTTP Status: " . $e->getHttpStatusCode() . "\n";

    if ($e->isClientError()) {
        echo "This is a client error (4xx) - check your request\n";
    } elseif ($e->isServerError()) {
        echo "This is a server error (5xx) - try again later\n";
    }

} catch (TCXCException $e) {
    // Catch any other TCXC exceptions
    echo "TCXC Error: " . $e->getMessage() . "\n";

} catch (Exception $e) {
    // Catch unexpected errors
    echo "Unexpected Error: " . $e->getMessage() . "\n";
}
```

**Output (validation error):**
```
Validation Error: Invalid destination phone number format
  - destination: Must be a valid E.164 phone number (7-15 digits)
```

---

## Example 5: Custom Carriers

**Scenario**: Add your own carrier mappings

```php
<?php

require_once 'vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;

$client = TCXCClientFactory::createFromEnv();

// Option 1: Add carriers one by one
$carriers = new CarrierRegistry();
$carriers->addCarrier('MY_PREMIUM_CARRIER', 9999);
$carriers->addCarrier('MY_BUDGET_CARRIER', 8888);

// Option 2: Create with custom carriers
$carriers = new CarrierRegistry([
    'PREMIUM_ROUTE' => 9999,
    'BUDGET_ROUTE' => 8888,
    'BACKUP_ROUTE' => 7777,
]);

// Still has access to defaults (TATA, VIBER, IBASIS)
echo "TATA ID: " . $carriers->getCarrierId('TATA') . "\n";
echo "My Premium: " . $carriers->getCarrierId('PREMIUM_ROUTE') . "\n";

// Use in call
$request = CallRequest::builder()
    ->setLegADestination('19542405555')
    ->setLegACallerId('18009999999')
    ->setLegAConnectionId($carriers->getCarrierId('PREMIUM_ROUTE'))
    ->setLegBDestination('18667471117')
    ->setLegBCallerId('12345678988')
    ->setLegBConnectionId($carriers->getCarrierId('BUDGET_ROUTE'))
    ->build();

$response = $client->initiateCall($request);
```

**Option 3: Load from file**

Create `config/carriers.json`:
```json
{
    "PREMIUM_ROUTE": 9999,
    "BUDGET_ROUTE": 8888,
    "BACKUP_ROUTE": 7777
}
```

```php
<?php
use TCXC\Config\CarrierRegistry;

$carriers = CarrierRegistry::fromFile('config/carriers.json');
```

---

## Example 6: With Logging

**Scenario**: Debug API calls with custom logger

```php
<?php

require_once 'vendor/autoload.php';

use TCXC\Client\TCXCClient;
use TCXC\Config\Configuration;
use TCXC\Model\CallRequest;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create custom logger
$logger = new Logger('tcxc-app');
$logger->pushHandler(new StreamHandler('app.log', Logger::DEBUG));
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// Create client with logger
$config = Configuration::fromEnv();
$client = new TCXCClient($config, $logger);

// Make call - all API activity will be logged
$request = CallRequest::builder()
    ->setLegADestination('19542405555')
    ->setLegACallerId('18009999999')
    ->setLegAConnectionId(220)
    ->setLegBDestination('18667471117')
    ->setLegBCallerId('12345678988')
    ->setLegBConnectionId(1771)
    ->build();

$response = $client->initiateCall($request);

// Check logs for detailed request/response info
```

**Log Output:**
```
[2024-01-15 10:30:45] tcxc-app.INFO: Initiating call {"leg_a":{"destination":"19542405555",...}}
[2024-01-15 10:30:45] tcxc-app.DEBUG: Making API request {"uri":"/api/callback/initiate/..."}
[2024-01-15 10:30:46] tcxc-app.DEBUG: API response received {"status_code":200,"body":"..."}
[2024-01-15 10:30:46] tcxc-app.INFO: Call initiated successfully {"call_id":"abc123xyz"}
```

---

## Example 7: Phone Number Formats

**Scenario**: The SDK automatically normalizes phone numbers

```php
<?php

use TCXC\Model\CallLeg;

// All these formats work - they're normalized to E.164
$validFormats = [
    '19542405555',           // Already normalized
    '+1 954 240 5555',       // With country code
    '1-954-240-5555',        // With dashes
    '(954) 240-5555',        // With parentheses
    '+1 (954) 240-5555',     // Mixed format
];

foreach ($validFormats as $number) {
    $leg = new CallLeg($number, '18009999999', 220);
    echo "Input: $number => Normalized: " . $leg->getDestination() . "\n";
}
```

**Output:**
```
Input: 19542405555 => Normalized: 19542405555
Input: +1 954 240 5555 => Normalized: 19542405555
Input: 1-954-240-5555 => Normalized: 19542405555
Input: (954) 240-5555 => Normalized: 9542405555
Input: +1 (954) 240-5555 => Normalized: 19542405555
```

---

## Common Use Cases

### Making a Click-to-Call
```php
// In your web form handler
$userPhone = $_POST['phone'];
$destinationPhone = '+1-800-COMPANY';

$request = CallRequest::builder()
    ->setLegADestination($userPhone)
    ->setLegACallerId($destinationPhone)
    ->setLegAConnectionId($carriers->getCarrierId('TATA'))
    ->setLegBDestination($destinationPhone)
    ->setLegBCallerId($userPhone)
    ->setLegBConnectionId($carriers->getCarrierId('VIBER'))
    ->setMetadata(['source' => 'web_form', 'user_id' => $_SESSION['user_id']])
    ->build();

$response = $client->initiateCall($request);
```

### CRM Integration
```php
// Integrate with your CRM
function initiateCallFromCRM($customerId, $agentId) {
    global $client, $carriers, $crm;

    $customer = $crm->getCustomer($customerId);
    $agent = $crm->getAgent($agentId);

    $request = CallRequest::builder()
        ->setLegADestination($customer->phone)
        ->setLegACallerId($agent->displayNumber)
        ->setLegAConnectionId($carriers->getCarrierId('TATA'))
        ->setLegBDestination($agent->phone)
        ->setLegBCallerId($customer->phone)
        ->setLegBConnectionId($carriers->getCarrierId('VIBER'))
        ->setMetadata([
            'customer_id' => $customerId,
            'agent_id' => $agentId,
            'call_type' => 'outbound_sales',
            'timestamp' => time()
        ])
        ->build();

    try {
        $response = $client->initiateCall($request);

        // Log to CRM
        $crm->logCall([
            'call_id' => $response->getCallId(),
            'customer_id' => $customerId,
            'agent_id' => $agentId,
            'status' => 'initiated'
        ]);

        return $response;
    } catch (TCXCException $e) {
        $crm->logError($e->getMessage());
        throw $e;
    }
}
```

---

## Best Practices

### ✅ DO
- Always use `.env` for credentials in production
- Enable SSL verification (`ssl_verify => true`)
- Implement proper error handling
- Use the builder pattern for readability
- Add metadata to track calls
- Log API calls for debugging

### ❌ DON'T
- Don't hardcode credentials in code
- Don't disable SSL verification in production
- Don't ignore validation errors
- Don't make calls without error handling
- Don't use raw phone numbers without validation

---

## Troubleshooting

### "Required configuration is missing"
```bash
# Make sure .env file exists
cp .env.example .env
# Add your credentials to .env
```

### "Authentication failed"
```php
// Check your credentials
$config = Configuration::fromEnv();
echo "Login: " . $config->getLogin() . "\n";
echo "Host: " . $config->getHost() . "\n";
// API key is not echoed for security
```

### "Invalid phone number format"
```php
// Phone must be 7-15 digits in E.164 format
// Valid: 19542405555 (country code + area code + number)
// Invalid: 5555555 (too short)
// Invalid: abc1234 (contains letters)
```

---

## Next Steps

- Read the [Full API Documentation](README.md)
- Check [Contributing Guidelines](CONTRIBUTING.md)
- View [Changelog](CHANGELOG.md) for updates
- Run the examples in `public/` directory

## Support

- **Issues**: [GitHub Issues](https://github.com/yourusername/tcxc-php-quickstart/issues)
- **API Key**: [Get TCXC API Key](https://www.telecomsxchange.com)
- **Documentation**: [TCXC Docs](https://docs.telecomsxchange.com)
