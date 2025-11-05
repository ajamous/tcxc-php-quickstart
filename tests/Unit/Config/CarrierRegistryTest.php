<?php

declare(strict_types=1);

namespace TCXC\Tests\Unit\Config;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TCXC\Config\CarrierRegistry;

class CarrierRegistryTest extends TestCase
{
    public function testCanGetDefaultCarriers(): void
    {
        $registry = new CarrierRegistry();

        $this->assertSame(220, $registry->getCarrierId('TATA'));
        $this->assertSame(1771, $registry->getCarrierId('VIBER'));
        $this->assertSame(234, $registry->getCarrierId('IBASIS'));
    }

    public function testCarrierNamesAreCaseInsensitive(): void
    {
        $registry = new CarrierRegistry();

        $this->assertSame(220, $registry->getCarrierId('tata'));
        $this->assertSame(220, $registry->getCarrierId('TaTa'));
        $this->assertSame(220, $registry->getCarrierId('TATA'));
    }

    public function testCanAddCustomCarrier(): void
    {
        $registry = new CarrierRegistry();
        $registry->addCarrier('CUSTOM', 999);

        $this->assertSame(999, $registry->getCarrierId('CUSTOM'));
    }

    public function testCanCreateWithCustomCarriers(): void
    {
        $registry = new CarrierRegistry([
            'CUSTOM1' => 100,
            'CUSTOM2' => 200,
        ]);

        $this->assertSame(100, $registry->getCarrierId('CUSTOM1'));
        $this->assertSame(200, $registry->getCarrierId('CUSTOM2'));
        $this->assertSame(220, $registry->getCarrierId('TATA')); // Default still available
    }

    public function testHasCarrierReturnsTrueForExistingCarrier(): void
    {
        $registry = new CarrierRegistry();

        $this->assertTrue($registry->hasCarrier('TATA'));
        $this->assertTrue($registry->hasCarrier('tata'));
        $this->assertFalse($registry->hasCarrier('NONEXISTENT'));
    }

    public function testThrowsExceptionForNonexistentCarrier(): void
    {
        $registry = new CarrierRegistry();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Carrier "NONEXISTENT" not found');

        $registry->getCarrierId('NONEXISTENT');
    }

    public function testThrowsExceptionForInvalidConnectionId(): void
    {
        $registry = new CarrierRegistry();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Connection ID must be a positive integer');

        $registry->addCarrier('INVALID', -1);
    }

    public function testGetAllCarriersReturnsAllRegisteredCarriers(): void
    {
        $registry = new CarrierRegistry(['CUSTOM' => 999]);
        $carriers = $registry->getAllCarriers();

        $this->assertArrayHasKey('TATA', $carriers);
        $this->assertArrayHasKey('VIBER', $carriers);
        $this->assertArrayHasKey('IBASIS', $carriers);
        $this->assertArrayHasKey('CUSTOM', $carriers);
    }

    public function testGetCarrierNameReturnsNameForValidId(): void
    {
        $registry = new CarrierRegistry();

        $this->assertSame('TATA', $registry->getCarrierName(220));
        $this->assertSame('VIBER', $registry->getCarrierName(1771));
        $this->assertNull($registry->getCarrierName(99999));
    }
}
