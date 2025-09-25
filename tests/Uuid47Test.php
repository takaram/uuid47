<?php
declare(strict_types=1);

namespace Takaram\Uuid47\Tests;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Takaram\Uuid47\Uuid47;

class Uuid47Test extends TestCase
{
    public function testEncode(): void
    {
        // UUIDv7 and key from https://github.com/stateless-me/uuidv47/blob/1f2e329c33b785f18b682df355da1fc563bc0dda/demo.c
        $v7 = Uuid::fromString('018f2d9f-9a2a-7def-8c3f-7b1a2c4d5e6f');
        $key = hex2bin('0123456789abcdeffedcba9876543210');

        $v4 = Uuid47::encode($v7, $key);

        $this->assertSame('2463c780-7fca-4def-8c3f-7b1a2c4d5e6f', $v4->toString());
    }

    public function testDecode(): void
    {
        $v4 = Uuid::fromString('2463c780-7fca-4def-8c3f-7b1a2c4d5e6f');
        $key = hex2bin('0123456789abcdeffedcba9876543210');

        $v7 = Uuid47::decode($v4, $key);

        $this->assertSame('018f2d9f-9a2a-7def-8c3f-7b1a2c4d5e6f', $v7->toString());
    }

    public function testEncodingIsInvertible(): void
    {
        $v7 = Uuid::uuid7();
        $key = random_bytes(16);

        $v4 = Uuid47::encode($v7, $key);
        $decodedV7 = Uuid47::decode($v4, $key);

        self::assertTrue($v7->equals($decodedV7), "$v7 and $decodedV7 should be equal");
    }

}
