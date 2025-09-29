<?php
declare(strict_types=1);

namespace Takaram\Uuid47\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use Ramsey\Uuid\Uuid;
use Takaram\Uuid47\Codec;
use function random_bytes;

class CodecTest extends TestCase
{
    private Codec $codec;

    protected function setUp(): void
    {
        parent::setUp();
        $this->codec = new Codec(random_bytes(16));
    }

    public function testConstructorWithInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Codec('too short');
    }

    public function testEncodeDecodeIsReversible(): void
    {
        $v7 = Uuid::uuid7();
        $v4 = $this->codec->encode($v7);
        $decodedV7 = $this->codec->decode($v4);

        $this->assertTrue($v7->equals($decodedV7));
        $this->assertNotTrue($v7->equals($v4));
    }

    public function testEncodedUuidIsVersion4(): void
    {
        $v7 = Uuid::uuid7();
        $v4 = $this->codec->encode($v7);

        $fields = $v4->getFields();
        $this->assertInstanceOf(Rfc4122FieldsInterface::class, $fields);
        $this->assertSame(4, $fields->getVersion());
    }

    public function testDecodedUuidIsVersion7(): void
    {
        $v7 = Uuid::uuid7();
        $v4 = $this->codec->encode($v7);
        $decodedV7 = $this->codec->decode($v4);

        $fields = $decodedV7->getFields();
        $this->assertInstanceOf(Rfc4122FieldsInterface::class, $fields);
        $this->assertSame(7, $fields->getVersion());
    }

    public function testEncodeWithDifferentKeysProducesDifferentUuids(): void
    {
        $v7 = Uuid::uuid7();
        $anotherCodec = new Codec(random_bytes(16));

        $uuid1 = $this->codec->encode($v7);
        $uuid2 = $anotherCodec->encode($v7);

        $this->assertFalse($uuid1->equals($uuid2));
    }

    public function testDecodeWithDifferentKeyFails(): void
    {
        $v7 = Uuid::uuid7();
        $v4 = $this->codec->encode($v7);

        $anotherCodec = new Codec(random_bytes(16));
        $decodedV7 = $anotherCodec->decode($v4);

        $this->assertFalse($v7->equals($decodedV7));
    }
}
