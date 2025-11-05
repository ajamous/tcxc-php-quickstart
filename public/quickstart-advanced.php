<?php

/**
 * TCXC PHP SDK - Advanced Quick Start Example
 *
 * Demonstrates:
 * - Comprehensive error handling
 * - Custom carriers
 * - Phone number validation
 * - Logging
 * - Multiple call scenarios
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use TCXC\Client\TCXCClient;
use TCXC\Config\Configuration;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;
use TCXC\Exception\ValidationException;
use TCXC\Exception\AuthenticationException;
use TCXC\Exception\RateLimitException;
use TCXC\Exception\ApiException;
use TCXC\Exception\TCXCException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// ============================================================================
// Setup
// ============================================================================

echo "╔════════════════════════════════════════════╗\n";
echo "║  TCXC PHP SDK - Advanced Quick Start      ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

// Create logger
$logger = new Logger('tcxc-demo');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/quickstart.log', Logger::DEBUG));

// Create client with logger
$config = Configuration::fromEnv(__DIR__ . '/..');
$client = new TCXCClient($config, $logger);

// Create carrier registry with custom carriers
$carriers = new CarrierRegistry([
    'MY_PREMIUM' => 9999,
    'MY_BACKUP' => 8888,
]);

echo "Environment: " . $config->getEnvironment() . "\n";
echo "Debug Mode: " . ($config->isDebug() ? 'ON' : 'OFF') . "\n";
echo "SSL Verify: " . ($config->isSslVerifyEnabled() ? 'ON' : 'OFF') . "\n\n";

// ============================================================================
// Helper Function
// ============================================================================

/**
 * Make a call with full error handling
 */
function makeCall(TCXCClient $client, CallRequest $request, string $scenario): void
{
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Scenario: $scenario\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    try {
        $response = $client->initiateCall($request);

        if ($response->isSuccess()) {
            echo "✓ SUCCESS\n";
            echo "  Call ID: " . ($response->getCallId() ?? 'N/A') . "\n";
            echo "  Message: " . ($response->getMessage() ?? 'OK') . "\n";
        } else {
            echo "✗ FAILED\n";
            echo "  Message: " . ($response->getMessage() ?? 'Unknown') . "\n";
        }

    } catch (ValidationException $e) {
        echo "✗ VALIDATION ERROR\n";
        echo "  Message: " . $e->getMessage() . "\n";

        if ($errors = $e->getErrors()) {
            echo "  Fields:\n";
            foreach ($errors as $field => $messages) {
                echo "    • $field: " . implode(', ', $messages) . "\n";
            }
        }

    } catch (AuthenticationException $e) {
        echo "✗ AUTHENTICATION ERROR\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  Tip: Check your TCXC_LOGIN and TCXC_API_KEY in .env\n";

    } catch (RateLimitException $e) {
        echo "✗ RATE LIMIT EXCEEDED\n";
        echo "  Message: " . $e->getMessage() . "\n";

        if ($retryAfter = $e->getRetryAfter()) {
            echo "  Retry after: $retryAfter seconds\n";
            echo "  Tip: Implement exponential backoff\n";
        }

    } catch (ApiException $e) {
        echo "✗ API ERROR\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  HTTP Status: " . ($e->getHttpStatusCode() ?? 'N/A') . "\n";

        if ($e->isClientError()) {
            echo "  Type: Client Error (4xx) - Check your request\n";
        } elseif ($e->isServerError()) {
            echo "  Type: Server Error (5xx) - Try again later\n";
        }

    } catch (TCXCException $e) {
        echo "✗ TCXC ERROR\n";
        echo "  Message: " . $e->getMessage() . "\n";

    } catch (Exception $e) {
        echo "✗ UNEXPECTED ERROR\n";
        echo "  Message: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

// ============================================================================
// Scenario 1: Standard Call
// ============================================================================

$request1 = CallRequest::builder()
    ->setLegADestination('19542405555')
    ->setLegACallerId('18009999999')
    ->setLegAConnectionId($carriers->getCarrierId('TATA'))
    ->setLegBDestination('18667471117')
    ->setLegBCallerId('12345678988')
    ->setLegBConnectionId($carriers->getCarrierId('VIBER'))
    ->setMetadata(['scenario' => 'standard_call'])
    ->build();

makeCall($client, $request1, 'Standard Call (TATA → VIBER)');

// ============================================================================
// Scenario 2: Using Custom Carrier
// ============================================================================

try {
    $request2 = CallRequest::builder()
        ->setLegADestination('19542405555')
        ->setLegACallerId('18009999999')
        ->setLegAConnectionId($carriers->getCarrierId('MY_PREMIUM'))
        ->setLegBDestination('18667471117')
        ->setLegBCallerId('12345678988')
        ->setLegBConnectionId($carriers->getCarrierId('MY_BACKUP'))
        ->setMetadata(['scenario' => 'custom_carriers'])
        ->build();

    makeCall($client, $request2, 'Custom Carriers (MY_PREMIUM → MY_BACKUP)');

} catch (Exception $e) {
    echo "Could not create request: " . $e->getMessage() . "\n\n";
}

// ============================================================================
// Scenario 3: Different Phone Formats (all normalized automatically)
// ============================================================================

$phoneFormats = [
    ['19542405555', 'Already normalized'],
    ['+1 954 240 5555', 'With country code and spaces'],
    ['1-954-240-5555', 'With dashes'],
    ['(954) 240-5555', 'With parentheses'],
];

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Scenario: Phone Number Normalization\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

foreach ($phoneFormats as [$phone, $description]) {
    try {
        $request = CallRequest::builder()
            ->setLegADestination($phone)
            ->setLegACallerId('18009999999')
            ->setLegAConnectionId(220)
            ->setLegBDestination('18667471117')
            ->setLegBCallerId('12345678988')
            ->setLegBConnectionId(1771)
            ->build();

        $normalized = $request->getLegA()->getDestination();
        echo "✓ $description\n";
        echo "  Input: $phone\n";
        echo "  Normalized: $normalized\n\n";

    } catch (ValidationException $e) {
        echo "✗ $description\n";
        echo "  Input: $phone\n";
        echo "  Error: " . $e->getMessage() . "\n\n";
    }
}

// ============================================================================
// Scenario 4: Invalid Phone Number (demonstrates validation)
// ============================================================================

try {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Scenario: Invalid Phone Number (Testing Validation)\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    $request = CallRequest::builder()
        ->setLegADestination('invalid-phone')  // Invalid!
        ->setLegACallerId('18009999999')
        ->setLegAConnectionId(220)
        ->setLegBDestination('18667471117')
        ->setLegBCallerId('12345678988')
        ->setLegBConnectionId(1771)
        ->build();

    // This won't be reached
    makeCall($client, $request, 'Invalid Phone');

} catch (ValidationException $e) {
    echo "✓ Validation caught the error (as expected)\n";
    echo "  Message: " . $e->getMessage() . "\n";

    foreach ($e->getErrors() as $field => $errors) {
        echo "  • $field: " . implode(', ', $errors) . "\n";
    }
    echo "\n";
}

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════╗\n";
echo "║           Examples Complete!               ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

echo "Key Takeaways:\n";
echo "  1. Use builder pattern for readable code\n";
echo "  2. Always implement error handling\n";
echo "  3. Phone numbers are auto-normalized\n";
echo "  4. Custom carriers can be easily added\n";
echo "  5. Logging helps with debugging\n\n";

echo "Check logs/quickstart.log for detailed logs.\n";
