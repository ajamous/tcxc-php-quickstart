<?php

/**
 * TCXC PHP SDK - Web Application Example
 *
 * Demonstrates how to integrate TCXC into a web application
 * for click-to-call functionality.
 *
 * Usage: php -S localhost:8000 -t public quickstart-webapp.php
 * Then visit: http://localhost:8000/quickstart-webapp.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use TCXC\Client\TCXCClientFactory;
use TCXC\Config\CarrierRegistry;
use TCXC\Model\CallRequest;
use TCXC\Exception\ValidationException;
use TCXC\Exception\TCXCException;

// ============================================================================
// Configuration
// ============================================================================

header('Content-Type: text/html; charset=utf-8');

// Initialize SDK
$client = null;
$carriers = null;
$error = null;

try {
    $client = TCXCClientFactory::createFromEnv(__DIR__ . '/..');
    $carriers = new CarrierRegistry();
} catch (Exception $e) {
    $error = "SDK initialization failed: " . $e->getMessage();
}

// ============================================================================
// Handle Form Submission
// ============================================================================

$result = null;
$validationErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $client !== null) {
    try {
        // Get form data
        $legAPhone = trim($_POST['leg_a_phone'] ?? '');
        $legACallerId = trim($_POST['leg_a_caller_id'] ?? '');
        $legACarrier = trim($_POST['leg_a_carrier'] ?? 'TATA');

        $legBPhone = trim($_POST['leg_b_phone'] ?? '');
        $legBCallerId = trim($_POST['leg_b_caller_id'] ?? '');
        $legBCarrier = trim($_POST['leg_b_carrier'] ?? 'VIBER');

        $campaign = trim($_POST['campaign'] ?? '');

        // Build request
        $request = CallRequest::builder()
            ->setLegADestination($legAPhone)
            ->setLegACallerId($legACallerId)
            ->setLegAConnectionId($carriers->getCarrierId($legACarrier))
            ->setLegBDestination($legBPhone)
            ->setLegBCallerId($legBCallerId)
            ->setLegBConnectionId($carriers->getCarrierId($legBCarrier))
            ->setMetadata([
                'source' => 'web_demo',
                'campaign' => $campaign,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s'),
            ])
            ->build();

        // Initiate call
        $response = $client->initiateCall($request);
        $result = [
            'success' => $response->isSuccess(),
            'call_id' => $response->getCallId(),
            'message' => $response->getMessage(),
        ];

    } catch (ValidationException $e) {
        $validationErrors = $e->getErrors();
        $result = [
            'success' => false,
            'message' => $e->getMessage(),
        ];

    } catch (TCXCException $e) {
        $result = [
            'success' => false,
            'message' => 'API Error: ' . $e->getMessage(),
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TCXC PHP SDK - Click-to-Call Demo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .content {
            padding: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌐 TCXC Click-to-Call Demo</h1>
            <p>Enterprise PHP SDK - Web Integration Example</p>
        </div>

        <div class="content">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($result): ?>
                <?php if ($result['success']): ?>
                    <div class="alert alert-success">
                        <strong>✓ Success!</strong> Call initiated successfully.<br>
                        <strong>Call ID:</strong> <?= htmlspecialchars($result['call_id'] ?? 'N/A') ?><br>
                        <strong>Message:</strong> <?= htmlspecialchars($result['message'] ?? 'OK') ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-error">
                        <strong>✗ Failed:</strong> <?= htmlspecialchars($result['message']) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($client): ?>
                <form method="POST">
                    <!-- Leg A Section -->
                    <div class="form-section">
                        <h2>📞 Leg A (First Participant)</h2>

                        <div class="grid">
                            <div class="form-group">
                                <label for="leg_a_phone">Phone Number *</label>
                                <input
                                    type="text"
                                    id="leg_a_phone"
                                    name="leg_a_phone"
                                    value="<?= htmlspecialchars($_POST['leg_a_phone'] ?? '19542405555') ?>"
                                    placeholder="19542405555"
                                    required
                                >
                                <?php if (isset($validationErrors['leg_a_destination'])): ?>
                                    <div class="error"><?= implode(', ', $validationErrors['leg_a_destination']) ?></div>
                                <?php endif; ?>
                                <div class="help-text">E.164 format (e.g., 19542405555)</div>
                            </div>

                            <div class="form-group">
                                <label for="leg_a_caller_id">Caller ID *</label>
                                <input
                                    type="text"
                                    id="leg_a_caller_id"
                                    name="leg_a_caller_id"
                                    value="<?= htmlspecialchars($_POST['leg_a_caller_id'] ?? '18009999999') ?>"
                                    placeholder="18009999999"
                                    required
                                >
                                <?php if (isset($validationErrors['leg_a_caller_id'])): ?>
                                    <div class="error"><?= implode(', ', $validationErrors['leg_a_caller_id']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="leg_a_carrier">Carrier</label>
                            <select id="leg_a_carrier" name="leg_a_carrier">
                                <?php foreach ($carriers->getAllCarriers() as $name => $id): ?>
                                    <option value="<?= htmlspecialchars($name) ?>"
                                        <?= ($_POST['leg_a_carrier'] ?? 'TATA') === $name ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?> (ID: <?= $id ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Leg B Section -->
                    <div class="form-section">
                        <h2>📞 Leg B (Second Participant)</h2>

                        <div class="grid">
                            <div class="form-group">
                                <label for="leg_b_phone">Phone Number *</label>
                                <input
                                    type="text"
                                    id="leg_b_phone"
                                    name="leg_b_phone"
                                    value="<?= htmlspecialchars($_POST['leg_b_phone'] ?? '18667471117') ?>"
                                    placeholder="18667471117"
                                    required
                                >
                                <?php if (isset($validationErrors['leg_b_destination'])): ?>
                                    <div class="error"><?= implode(', ', $validationErrors['leg_b_destination']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="leg_b_caller_id">Caller ID *</label>
                                <input
                                    type="text"
                                    id="leg_b_caller_id"
                                    name="leg_b_caller_id"
                                    value="<?= htmlspecialchars($_POST['leg_b_caller_id'] ?? '12345678988') ?>"
                                    placeholder="12345678988"
                                    required
                                >
                                <?php if (isset($validationErrors['leg_b_caller_id'])): ?>
                                    <div class="error"><?= implode(', ', $validationErrors['leg_b_caller_id']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="leg_b_carrier">Carrier</label>
                            <select id="leg_b_carrier" name="leg_b_carrier">
                                <?php foreach ($carriers->getAllCarriers() as $name => $id): ?>
                                    <option value="<?= htmlspecialchars($name) ?>"
                                        <?= ($_POST['leg_b_carrier'] ?? 'VIBER') === $name ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?> (ID: <?= $id ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Metadata Section -->
                    <div class="form-section">
                        <h2>📋 Additional Info</h2>

                        <div class="form-group">
                            <label for="campaign">Campaign/Reference</label>
                            <input
                                type="text"
                                id="campaign"
                                name="campaign"
                                value="<?= htmlspecialchars($_POST['campaign'] ?? 'demo_campaign') ?>"
                                placeholder="e.g., sales_outbound, customer_support"
                            >
                            <div class="help-text">Optional tracking reference</div>
                        </div>
                    </div>

                    <button type="submit" class="btn">🚀 Initiate Call</button>
                </form>

                <div class="alert alert-info" style="margin-top: 20px;">
                    <strong>💡 Tip:</strong> This is a demo interface for the TCXC PHP SDK.
                    In production, you would typically trigger calls programmatically from your application.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
