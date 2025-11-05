<?php

declare(strict_types=1);

namespace TCXC\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use TCXC\Config\Configuration;
use TCXC\Exception\ApiException;
use TCXC\Exception\AuthenticationException;
use TCXC\Exception\RateLimitException;
use TCXC\Model\CallRequest;
use TCXC\Model\CallResponse;

/**
 * Main client for interacting with the TCXC API.
 *
 * Provides methods for initiating calls and managing voice communication
 * through the TelecomsXChange platform.
 */
class TCXCClient
{
    private Configuration $config;
    private GuzzleClient $httpClient;
    private LoggerInterface $logger;

    /**
     * Create a new TCXCClient instance.
     *
     * @param Configuration $config Configuration instance
     * @param LoggerInterface|null $logger Optional logger instance
     */
    public function __construct(Configuration $config, ?LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
        $this->httpClient = $this->createHttpClient();
    }

    /**
     * Initiate a bridge call between two phone numbers.
     *
     * @param CallRequest $request Call request with leg A and leg B
     * @return CallResponse Call response with status and details
     * @throws ApiException If API request fails
     * @throws AuthenticationException If authentication fails
     * @throws RateLimitException If rate limit is exceeded
     */
    public function initiateCall(CallRequest $request): CallResponse
    {
        $this->logger->info('Initiating call', [
            'leg_a' => $request->getLegA()->toArray(),
            'leg_b' => $request->getLegB()->toArray(),
        ]);

        try {
            $uri = $this->buildCallUri($request);
            $signature = $this->generateSignature($uri);
            $fullUri = $uri . $signature . '/';

            $this->logger->debug('Making API request', [
                'uri' => $fullUri,
                'host' => $this->config->getHost(),
            ]);

            $response = $this->httpClient->get($fullUri);
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            $this->logger->debug('API response received', [
                'status_code' => $statusCode,
                'body' => $body,
            ]);

            // Try to parse JSON response
            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If not JSON, treat as plain text success message
                $data = [
                    'success' => $statusCode === 200,
                    'message' => $body,
                ];
            }

            $callResponse = CallResponse::fromArray($data);

            $this->logger->info('Call initiated successfully', [
                'call_id' => $callResponse->getCallId(),
                'success' => $callResponse->isSuccess(),
            ]);

            return $callResponse;
        } catch (RequestException $e) {
            return $this->handleRequestException($e);
        } catch (GuzzleException $e) {
            $this->logger->error('HTTP client error', [
                'error' => $e->getMessage(),
            ]);

            throw new ApiException(
                'Failed to communicate with TCXC API: ' . $e->getMessage(),
                null,
                null,
                0,
                $e
            );
        }
    }

    /**
     * Build the API URI for call initiation.
     *
     * @param CallRequest $request Call request
     * @return string URI without signature
     */
    private function buildCallUri(CallRequest $request): string
    {
        $legA = $request->getLegA();
        $legB = $request->getLegB();
        $timestamp = time();

        return sprintf(
            '/api/callback/initiate/%s/%d/%s/%s/%d/%s/%s/%d/%d/',
            $this->config->getLogin(),
            $this->config->getAccountId(),
            $legA->getDestination(),
            $legA->getCallerId(),
            $legA->getConnectionId(),
            $legB->getDestination(),
            $legB->getCallerId(),
            $legB->getConnectionId(),
            $timestamp
        );
    }

    /**
     * Generate HMAC-SHA256 signature for request authentication.
     *
     * @param string $uri Request URI
     * @return string Signature hash
     */
    private function generateSignature(string $uri): string
    {
        return hash('sha256', $uri . $this->config->getApiKey());
    }

    /**
     * Create and configure the Guzzle HTTP client.
     *
     * @return GuzzleClient
     */
    private function createHttpClient(): GuzzleClient
    {
        $options = [
            'base_uri' => $this->config->getHost(),
            'timeout' => $this->config->getHttpTimeout(),
            'connect_timeout' => $this->config->getHttpConnectTimeout(),
            'verify' => $this->config->isSslVerifyEnabled(),
            'headers' => [
                'User-Agent' => 'TCXC-PHP-SDK/2.0',
                'Accept' => 'application/json',
            ],
        ];

        if ($this->config->isDebug()) {
            $options['debug'] = true;
        }

        return new GuzzleClient($options);
    }

    /**
     * Handle HTTP request exceptions and convert to appropriate SDK exceptions.
     *
     * @param RequestException $e Request exception
     * @return never
     * @throws ApiException
     * @throws AuthenticationException
     * @throws RateLimitException
     */
    private function handleRequestException(RequestException $e): never
    {
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : null;
        $body = $response ? (string) $response->getBody() : null;

        $this->logger->error('API request failed', [
            'status_code' => $statusCode,
            'error' => $e->getMessage(),
            'response' => $body,
        ]);

        // Handle specific HTTP status codes
        if ($statusCode === 401 || $statusCode === 403) {
            throw new AuthenticationException(
                'Authentication failed: Invalid credentials or API key',
                0,
                $e
            );
        }

        if ($statusCode === 429) {
            $retryAfter = null;
            if ($response && $response->hasHeader('Retry-After')) {
                $retryAfter = (int) $response->getHeader('Retry-After')[0];
            }

            throw new RateLimitException(
                'Rate limit exceeded. Please try again later.',
                $retryAfter
            );
        }

        throw new ApiException(
            sprintf('API request failed: %s', $e->getMessage()),
            $statusCode,
            $body,
            0,
            $e
        );
    }

    /**
     * Get the configuration instance.
     *
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Set a custom logger.
     *
     * @param LoggerInterface $logger Logger instance
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
}
