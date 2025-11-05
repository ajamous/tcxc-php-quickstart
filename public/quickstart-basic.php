<?php

/**
 * TCXC PHP SDK - Basic Quick Start Example
 *
 * This is the simplest possible example to get you started.
 * Just update your credentials in .env and run: php public/quickstart-basic.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;
use TCXC\Exception\TCXCException;

echo "╔════════════════════════════════════════════╗\n";
echo "║   TCXC PHP SDK - Quick Start Example      ║\n";
echo "╚════════════════════════════════════════════╝\n\n";

try {
    // Step 1: Create client from .env file
    echo "→ Creating TCXC client...\n";
    $client = TCXCClientFactory::createFromEnv(__DIR__ . '/..');

    // Step 2: Get carrier registry
    echo "→ Loading carriers...\n";
    $carriers = new CarrierRegistry();
    echo "  Available carriers: " . implode(', ', array_keys($carriers->getAllCarriers())) . "\n\n";

    // Step 3: Build call request using fluent builder
    echo "→ Building call request...\n";
    $request = CallRequest::builder()
        // Leg A (First participant)
        ->setLegADestination('19542405555')                     // Phone to call
        ->setLegACallerId('18009999999')                        // Caller ID to show
        ->setLegAConnectionId($carriers->getCarrierId('TATA')) // Carrier to use

        // Leg B (Second participant)
        ->setLegBDestination('18667471117')
        ->setLegBCallerId('12345678988')
        ->setLegBConnectionId($carriers->getCarrierId('VIBER'))

        // Optional metadata
        ->setMetadata([
            'source' => 'quickstart_example',
            'timestamp' => date('Y-m-d H:i:s'),
        ])
        ->build();

    echo "  Leg A: " . $request->getLegA()->getDestination() . " (via TATA)\n";
    echo "  Leg B: " . $request->getLegB()->getDestination() . " (via VIBER)\n\n";

    // Step 4: Initiate the call
    echo "→ Initiating call...\n";
    $response = $client->initiateCall($request);

    // Step 5: Check response
    echo "\n╔════════════════════════════════════════════╗\n";
    if ($response->isSuccess()) {
        echo "║              ✓ SUCCESS!                    ║\n";
        echo "╚════════════════════════════════════════════╝\n\n";
        echo "Call ID: " . ($response->getCallId() ?? 'N/A') . "\n";
        echo "Message: " . ($response->getMessage() ?? 'Call initiated successfully') . "\n";
    } else {
        echo "║              ✗ FAILED                      ║\n";
        echo "╚════════════════════════════════════════════╝\n\n";
        echo "Message: " . ($response->getMessage() ?? 'Unknown error') . "\n";
    }

    // Show full response
    echo "\nFull Response:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $response->toJson() . "\n";

} catch (TCXCException $e) {
    echo "\n╔════════════════════════════════════════════╗\n";
    echo "║              ✗ ERROR                       ║\n";
    echo "╚════════════════════════════════════════════╝\n\n";

    echo "Error Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";

    // Show validation errors if present
    if ($e instanceof \TCXC\Exception\ValidationException) {
        echo "\nValidation Errors:\n";
        foreach ($e->getErrors() as $field => $errors) {
            echo "  • $field: " . implode(', ', $errors) . "\n";
        }
    }

    exit(1);

} catch (Exception $e) {
    echo "\n✗ Unexpected Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ Done!\n";
