<?php

declare(strict_types=1);

namespace TCXC\Model;

/**
 * Represents the response from a call initiation request.
 *
 * Contains information about the initiated call including status,
 * call ID, and any additional data returned by the API.
 */
class CallResponse
{
    private bool $success;
    private ?string $callId;
    private ?string $message;
    private array $rawResponse;

    /**
     * Create a new CallResponse instance.
     *
     * @param bool $success Whether the call was successfully initiated
     * @param string|null $callId Unique call identifier
     * @param string|null $message Response message
     * @param array<string, mixed> $rawResponse Raw API response
     */
    public function __construct(
        bool $success,
        ?string $callId = null,
        ?string $message = null,
        array $rawResponse = []
    ) {
        $this->success = $success;
        $this->callId = $callId;
        $this->message = $message;
        $this->rawResponse = $rawResponse;
    }

    /**
     * Check if call was successfully initiated.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get call ID.
     *
     * @return string|null
     */
    public function getCallId(): ?string
    {
        return $this->callId;
    }

    /**
     * Get response message.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Get raw API response.
     *
     * @return array<string, mixed>
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    /**
     * Get a specific value from the raw response.
     *
     * @param string $key Key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->rawResponse[$key] ?? $default;
    }

    /**
     * Create CallResponse from API response array.
     *
     * @param array<string, mixed> $response API response
     * @return self
     */
    public static function fromArray(array $response): self
    {
        // Handle different response formats
        $success = $response['success'] ?? $response['status'] ?? false;
        $callId = $response['call_id'] ?? $response['id'] ?? null;
        $message = $response['message'] ?? $response['msg'] ?? null;

        return new self($success, $callId, $message, $response);
    }

    /**
     * Convert CallResponse to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'call_id' => $this->callId,
            'message' => $this->message,
            'raw_response' => $this->rawResponse,
        ];
    }

    /**
     * Convert CallResponse to JSON.
     *
     * @return string
     */
    public function toJson(): string
    {
        $json = json_encode($this->toArray(), JSON_PRETTY_PRINT);

        return $json !== false ? $json : '{}';
    }
}
