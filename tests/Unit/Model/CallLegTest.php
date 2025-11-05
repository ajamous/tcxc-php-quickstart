<?php

declare(strict_types=1);

namespace TCXC\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use TCXC\Exception\ValidationException;
use TCXC\Model\CallLeg;

class CallLegTest extends TestCase
{
    public function testCanCreateCallLeg(): void
    {
        $leg = new CallLeg('19542405555', '18009999999', 220);

        $this->assertSame('19542405555', $leg->getDestination());
        $this->assertSame('18009999999', $leg->getCallerId());
        $this->assertSame(220, $leg->getConnectionId());
    }

    public function testNormalizesPhoneNumbers(): void
    {
        $leg = new CallLeg('+1 (954) 240-5555', '1-800-999-9999', 220);

        $this->assertSame('19542405555', $leg->getDestination());
        $this->assertSame('18009999999', $leg->getCallerId());
    }

    public function testThrowsExceptionForEmptyDestination(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Destination phone number cannot be empty');

        new CallLeg('', '18009999999', 220);
    }

    public function testThrowsExceptionForInvalidDestination(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid destination phone number format');

        new CallLeg('invalid', '18009999999', 220);
    }

    public function testThrowsExceptionForEmptyCallerId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Caller ID cannot be empty');

        new CallLeg('19542405555', '', 220);
    }

    public function testThrowsExceptionForInvalidCallerId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid caller ID format');

        new CallLeg('19542405555', 'invalid', 220);
    }

    public function testThrowsExceptionForInvalidConnectionId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid connection ID');

        new CallLeg('19542405555', '18009999999', -1);
    }

    public function testCanCreateFromArray(): void
    {
        $data = [
            'destination' => '19542405555',
            'caller_id' => '18009999999',
            'connection_id' => 220,
        ];

        $leg = CallLeg::fromArray($data);

        $this->assertSame('19542405555', $leg->getDestination());
        $this->assertSame('18009999999', $leg->getCallerId());
        $this->assertSame(220, $leg->getConnectionId());
    }

    public function testThrowsExceptionWhenCreatingFromArrayWithMissingFields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required fields');

        CallLeg::fromArray(['destination' => '19542405555']);
    }

    public function testCanConvertToArray(): void
    {
        $leg = new CallLeg('19542405555', '18009999999', 220);
        $array = $leg->toArray();

        $expected = [
            'destination' => '19542405555',
            'caller_id' => '18009999999',
            'connection_id' => 220,
        ];

        $this->assertSame($expected, $array);
    }
}
