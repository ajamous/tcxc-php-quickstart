<?php

declare(strict_types=1);

namespace TCXC\Model;

use TCXC\Exception\ValidationException;

/**
 * Represents a call request with two legs (bridge call).
 *
 * A bridge call connects two phone numbers together, with each leg
 * potentially using different carriers and caller IDs.
 */
class CallRequest
{
    private CallLeg $legA;
    private CallLeg $legB;
    private ?array $metadata;

    /**
     * Create a new CallRequest instance.
     *
     * @param CallLeg $legA First call leg (initiator)
     * @param CallLeg $legB Second call leg (recipient)
     * @param array<string, mixed>|null $metadata Optional metadata
     */
    public function __construct(CallLeg $legA, CallLeg $legB, ?array $metadata = null)
    {
        $this->legA = $legA;
        $this->legB = $legB;
        $this->metadata = $metadata;
    }

    /**
     * Get first call leg (Leg A).
     *
     * @return CallLeg
     */
    public function getLegA(): CallLeg
    {
        return $this->legA;
    }

    /**
     * Get second call leg (Leg B).
     *
     * @return CallLeg
     */
    public function getLegB(): CallLeg
    {
        return $this->legB;
    }

    /**
     * Get metadata.
     *
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * Set metadata.
     *
     * @param array<string, mixed> $metadata Metadata
     * @return self
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Create CallRequest from array.
     *
     * @param array<string, mixed> $data Data array
     * @return self
     * @throws ValidationException If required fields are missing
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['leg_a'])) {
            throw new ValidationException('Missing required field: leg_a', [
                'leg_a' => ['Leg A is required'],
            ]);
        }

        if (!isset($data['leg_b'])) {
            throw new ValidationException('Missing required field: leg_b', [
                'leg_b' => ['Leg B is required'],
            ]);
        }

        return new self(
            CallLeg::fromArray($data['leg_a']),
            CallLeg::fromArray($data['leg_b']),
            $data['metadata'] ?? null
        );
    }

    /**
     * Convert CallRequest to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'leg_a' => $this->legA->toArray(),
            'leg_b' => $this->legB->toArray(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Create a simple call request builder.
     *
     * @return CallRequestBuilder
     */
    public static function builder(): CallRequestBuilder
    {
        return new CallRequestBuilder();
    }
}

/**
 * Builder class for creating CallRequest instances.
 *
 * Provides a fluent interface for constructing call requests.
 */
class CallRequestBuilder
{
    private ?string $legADestination = null;
    private ?string $legACallerId = null;
    private ?int $legAConnectionId = null;
    private ?string $legBDestination = null;
    private ?string $legBCallerId = null;
    private ?int $legBConnectionId = null;
    private ?array $metadata = null;

    /**
     * Set Leg A destination.
     *
     * @param string $destination Phone number
     * @return self
     */
    public function setLegADestination(string $destination): self
    {
        $this->legADestination = $destination;

        return $this;
    }

    /**
     * Set Leg A caller ID.
     *
     * @param string $callerId Caller ID
     * @return self
     */
    public function setLegACallerId(string $callerId): self
    {
        $this->legACallerId = $callerId;

        return $this;
    }

    /**
     * Set Leg A connection ID.
     *
     * @param int $connectionId Connection ID
     * @return self
     */
    public function setLegAConnectionId(int $connectionId): self
    {
        $this->legAConnectionId = $connectionId;

        return $this;
    }

    /**
     * Set Leg B destination.
     *
     * @param string $destination Phone number
     * @return self
     */
    public function setLegBDestination(string $destination): self
    {
        $this->legBDestination = $destination;

        return $this;
    }

    /**
     * Set Leg B caller ID.
     *
     * @param string $callerId Caller ID
     * @return self
     */
    public function setLegBCallerId(string $callerId): self
    {
        $this->legBCallerId = $callerId;

        return $this;
    }

    /**
     * Set Leg B connection ID.
     *
     * @param int $connectionId Connection ID
     * @return self
     */
    public function setLegBConnectionId(int $connectionId): self
    {
        $this->legBConnectionId = $connectionId;

        return $this;
    }

    /**
     * Set metadata.
     *
     * @param array<string, mixed> $metadata Metadata
     * @return self
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Build the CallRequest.
     *
     * @return CallRequest
     * @throws ValidationException If required fields are not set
     */
    public function build(): CallRequest
    {
        $errors = [];

        if ($this->legADestination === null) {
            $errors['leg_a_destination'] = ['Leg A destination is required'];
        }
        if ($this->legACallerId === null) {
            $errors['leg_a_caller_id'] = ['Leg A caller ID is required'];
        }
        if ($this->legAConnectionId === null) {
            $errors['leg_a_connection_id'] = ['Leg A connection ID is required'];
        }
        if ($this->legBDestination === null) {
            $errors['leg_b_destination'] = ['Leg B destination is required'];
        }
        if ($this->legBCallerId === null) {
            $errors['leg_b_caller_id'] = ['Leg B caller ID is required'];
        }
        if ($this->legBConnectionId === null) {
            $errors['leg_b_connection_id'] = ['Leg B connection ID is required'];
        }

        if (!empty($errors)) {
            throw new ValidationException('Missing required fields for CallRequest', $errors);
        }

        // PHPStan assertions - these are guaranteed to be non-null after validation
        assert($this->legADestination !== null);
        assert($this->legACallerId !== null);
        assert($this->legAConnectionId !== null);
        assert($this->legBDestination !== null);
        assert($this->legBCallerId !== null);
        assert($this->legBConnectionId !== null);

        return new CallRequest(
            new CallLeg($this->legADestination, $this->legACallerId, $this->legAConnectionId),
            new CallLeg($this->legBDestination, $this->legBCallerId, $this->legBConnectionId),
            $this->metadata
        );
    }
}
