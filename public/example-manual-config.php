<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use TCXC\Client\TCXCClient;
use TCXC\Config\CarrierRegistry;
use TCXC\Config\Configuration;
use TCXC\Exception\TCXCException;
use TCXC\Model\CallLeg;
use TCXC\Model\CallRequest;

// Example: Creating client with manual configuration (no .env file required)
try {
    // Create configuration manually
    $config = new Configuration([
        'host' => 'https://members.telecomsxchange.com',
        'login' => 'your_username_here',
        'api_key' => 'your_api_key_here',
        'account_id' => 1,
        'environment' => 'production',
        'debug' => false,
        'ssl_verify' => true, // Always use SSL verification in production!
    ]);

    // Create client with configuration
    $client = new TCXCClient($config);

    // Create carrier registry with custom carriers
    $carriers = new CarrierRegistry([
        'MY_CUSTOM_CARRIER' => 12345,
    ]);

    // Create call legs manually
    $legA = new CallLeg(
        '19542405555',              // Destination
        '18009999999',              // Caller ID
        $carriers->getCarrierId('TATA')  // Carrier
    );

    $legB = new CallLeg(
        '18667471117',
        '12345678988',
        $carriers->getCarrierId('VIBER')
    );

    // Create call request
    $callRequest = new CallRequest($legA, $legB);

    // Initiate the call
    $response = $client->initiateCall($callRequest);

    // Display result
    echo "Call Status: " . ($response->isSuccess() ? 'Success' : 'Failed') . "\n";
    echo "Response:\n" . $response->toJson() . "\n";

} catch (TCXCException $e) {
    echo "TCXC Error: " . $e->getMessage() . "\n";
    exit(1);
}
