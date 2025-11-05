<?php

declare(strict_types=1);

namespace TCXC\Model;

use TCXC\Exception\ValidationException;

/**
 * Represents a call leg (participant) in a voice call.
 *
 * A call leg contains information about one participant in a call,
 * including their phone number, caller ID, and carrier routing.
 */
class CallLeg
{
    private string $destination;
    private string $callerId;
    private int $connectionId;

    /**
     * Create a new CallLeg instance.
     *
     * @param string $destination Destination phone number (CLD - Called Number)
     * @param string $callerId Caller ID to display (CLI - Calling Line Identification)
     * @param int $connectionId Carrier connection ID for routing
     * @throws ValidationException If validation fails
     */
    public function __construct(string $destination, string $callerId, int $connectionId)
    {
        $this->validateDestination($destination);
        $this->validateCallerId($callerId);
        $this->validateConnectionId($connectionId);

        $this->destination = $this->normalizePhoneNumber($destination);
        $this->callerId = $this->normalizePhoneNumber($callerId);
        $this->connectionId = $connectionId;
    }

    /**
     * Get destination phone number.
     *
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Get caller ID.
     *
     * @return string
     */
    public function getCallerId(): string
    {
        return $this->callerId;
    }

    /**
     * Get carrier connection ID.
     *
     * @return int
     */
    public function getConnectionId(): int
    {
        return $this->connectionId;
    }

    /**
     * Validate destination phone number.
     *
     * @param string $destination Phone number
     * @throws ValidationException If invalid
     */
    private function validateDestination(string $destination): void
    {
        if (empty($destination)) {
            throw new ValidationException('Destination phone number cannot be empty', [
                'destination' => ['Destination is required'],
            ]);
        }

        // Remove common formatting characters for validation
        $cleaned = preg_replace('/[\s\-\(\)]+/', '', $destination);

        if (!preg_match('/^\+?[1-9]\d{6,14}$/', $cleaned)) {
            throw new ValidationException('Invalid destination phone number format', [
                'destination' => ['Must be a valid E.164 phone number (7-15 digits)'],
            ]);
        }
    }

    /**
     * Validate caller ID.
     *
     * @param string $callerId Caller ID
     * @throws ValidationException If invalid
     */
    private function validateCallerId(string $callerId): void
    {
        if (empty($callerId)) {
            throw new ValidationException('Caller ID cannot be empty', [
                'caller_id' => ['Caller ID is required'],
            ]);
        }

        // Remove common formatting characters for validation
        $cleaned = preg_replace('/[\s\-\(\)]+/', '', $callerId);

        if (!preg_match('/^\+?[1-9]\d{6,14}$/', $cleaned)) {
            throw new ValidationException('Invalid caller ID format', [
                'caller_id' => ['Must be a valid E.164 phone number (7-15 digits)'],
            ]);
        }
    }

    /**
     * Validate connection ID.
     *
     * @param int $connectionId Connection ID
     * @throws ValidationException If invalid
     */
    private function validateConnectionId(int $connectionId): void
    {
        if ($connectionId <= 0) {
            throw new ValidationException('Invalid connection ID', [
                'connection_id' => ['Must be a positive integer'],
            ]);
        }
    }

    /**
     * Normalize phone number by removing formatting characters.
     *
     * @param string $phoneNumber Phone number
     * @return string Normalized phone number (digits only)
     */
    private function normalizePhoneNumber(string $phoneNumber): string
    {
        return preg_replace('/[\s\-\(\)+]+/', '', $phoneNumber);
    }

    /**
     * Create CallLeg from array.
     *
     * @param array<string, mixed> $data Data array
     * @return self
     * @throws ValidationException If required fields are missing
     */
    public static function fromArray(array $data): self
    {
        $required = ['destination', 'caller_id', 'connection_id'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new ValidationException(
                'Missing required fields: ' . implode(', ', $missing),
                array_fill_keys($missing, ['This field is required'])
            );
        }

        return new self(
            (string) $data['destination'],
            (string) $data['caller_id'],
            (int) $data['connection_id']
        );
    }

    /**
     * Convert CallLeg to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'destination' => $this->destination,
            'caller_id' => $this->callerId,
            'connection_id' => $this->connectionId,
        ];
    }
}
