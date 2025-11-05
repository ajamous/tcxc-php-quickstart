<?php

declare(strict_types=1);

namespace TCXC\Config;

use InvalidArgumentException;

/**
 * Registry for managing carrier connection IDs.
 *
 * Provides a centralized way to manage carrier configurations and mappings
 * between human-readable carrier names and their connection IDs.
 */
class CarrierRegistry
{
    /**
     * @var array<string, int> Default carrier mappings
     */
    private const DEFAULT_CARRIERS = [
        'TATA' => 220,
        'VIBER' => 1771,
        'IBASIS' => 234,
    ];

    /**
     * @var array<string, int> Custom carrier mappings
     */
    private array $carriers;

    /**
     * Create a new CarrierRegistry instance.
     *
     * @param array<string, int> $customCarriers Optional custom carrier mappings
     */
    public function __construct(array $customCarriers = [])
    {
        $this->carriers = array_merge(self::DEFAULT_CARRIERS, $customCarriers);
    }

    /**
     * Get carrier connection ID by name.
     *
     * @param string $name Carrier name (case-insensitive)
     * @return int Carrier connection ID
     * @throws InvalidArgumentException If carrier not found
     */
    public function getCarrierId(string $name): int
    {
        $normalizedName = strtoupper(trim($name));

        if (!isset($this->carriers[$normalizedName])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Carrier "%s" not found. Available carriers: %s',
                    $name,
                    implode(', ', array_keys($this->carriers))
                )
            );
        }

        return $this->carriers[$normalizedName];
    }

    /**
     * Check if a carrier exists in the registry.
     *
     * @param string $name Carrier name
     * @return bool
     */
    public function hasCarrier(string $name): bool
    {
        $normalizedName = strtoupper(trim($name));

        return isset($this->carriers[$normalizedName]);
    }

    /**
     * Add or update a carrier mapping.
     *
     * @param string $name Carrier name
     * @param int $connectionId Carrier connection ID
     * @return self
     * @throws InvalidArgumentException If connection ID is invalid
     */
    public function addCarrier(string $name, int $connectionId): self
    {
        if ($connectionId <= 0) {
            throw new InvalidArgumentException('Connection ID must be a positive integer');
        }

        $normalizedName = strtoupper(trim($name));
        $this->carriers[$normalizedName] = $connectionId;

        return $this;
    }

    /**
     * Get all registered carriers.
     *
     * @return array<string, int>
     */
    public function getAllCarriers(): array
    {
        return $this->carriers;
    }

    /**
     * Get carrier name by connection ID.
     *
     * @param int $connectionId Connection ID
     * @return string|null Carrier name or null if not found
     */
    public function getCarrierName(int $connectionId): ?string
    {
        $name = array_search($connectionId, $this->carriers, true);

        return $name !== false ? $name : null;
    }

    /**
     * Load carriers from configuration file.
     *
     * @param string $filePath Path to carrier configuration file (JSON or PHP)
     * @return self
     * @throws InvalidArgumentException If file cannot be loaded
     */
    public static function fromFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(
                sprintf('Carrier configuration file not found: %s', $filePath)
            );
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if ($extension === 'json') {
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new InvalidArgumentException('Failed to read carrier configuration file');
            }

            $carriers = json_decode($content, true);
            if (!is_array($carriers)) {
                throw new InvalidArgumentException('Invalid JSON in carrier configuration file');
            }

            return new self($carriers);
        }

        if ($extension === 'php') {
            $carriers = require $filePath;
            if (!is_array($carriers)) {
                throw new InvalidArgumentException('Carrier configuration file must return an array');
            }

            return new self($carriers);
        }

        throw new InvalidArgumentException('Carrier configuration file must be .json or .php');
    }
}
