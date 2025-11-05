<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Exception\TCXCException;
use TCXC\Model\CallLeg;
use TCXC\Model\CallRequest;

// Load environment variables from .env file
// Make sure to copy .env.example to .env and fill in your credentials
try {
    // Create client from environment variables
    $client = TCXCClientFactory::createFromEnv(__DIR__ . '/..');

    // Get carrier registry
    $carriers = new CarrierRegistry();

    // Build call request using the fluent builder pattern
    $callRequest = CallRequest::builder()
        ->setLegADestination('19542405555')
        ->setLegACallerId('18009999999')
        ->setLegAConnectionId($carriers->getCarrierId('TATA'))
        ->setLegBDestination('18667471117')
        ->setLegBCallerId('12345678988')
        ->setLegBConnectionId($carriers->getCarrierId('VIBER'))
        ->setMetadata([
            'campaign' => 'demo',
            'user_id' => '12345',
        ])
        ->build();

    // Initiate the call
    $response = $client->initiateCall($callRequest);

    // Check response
    if ($response->isSuccess()) {
        echo "✓ Call initiated successfully!\n";
        echo "Call ID: " . ($response->getCallId() ?? 'N/A') . "\n";
        echo "Message: " . ($response->getMessage() ?? 'N/A') . "\n";
    } else {
        echo "✗ Call failed\n";
        echo "Message: " . ($response->getMessage() ?? 'Unknown error') . "\n";
    }

    // Display full response
    echo "\nFull Response:\n";
    echo $response->toJson() . "\n";

} catch (TCXCException $e) {
    echo "Error: " . $e->getMessage() . "\n";

    if ($e instanceof \TCXC\Exception\ValidationException) {
        echo "Validation errors:\n";
        print_r($e->getErrors());
    }

    exit(1);
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
    exit(1);
}
