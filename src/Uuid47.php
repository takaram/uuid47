<?php
declare(strict_types=1);

namespace Takaram\Uuid47;

use Ramsey\Uuid\Generator\RandomGeneratorInterface;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;
use function assert;
use function chr;
use function ord;
use function sodium_crypto_shorthash;
use function strlen;
use function strrev;
use function substr;

final class Uuid47
{
    public static function encode(UuidInterface $v7, string $key): UuidInterface
    {
        $uuidBytes = $v7->getBytes();

        // 1) mask = SipHash24(key, v7.random74bits) -> take low 48 bits
        $sipMsg = self::buildSipInputFromV7($uuidBytes);
        $mask48 = substr(self::sipHash24($key, $sipMsg), 2, 6);

        // 2) encTS = ts ^ mask
        $ts48 = substr($uuidBytes, 0, 6);
        $encTs = $ts48 ^ $mask48;

        // 3) build v4 facade: write encTS, set ver=4, keep rand bytes identical, set variant
        $uuidWithoutVerVariant = $encTs . substr($uuidBytes, 6, 10);

        // Use UuidFactory from ramsey/uuid to set version and variant bits
        $uuidFactory = new UuidFactory();
        $uuidFactory->setRandomGenerator(new class($uuidWithoutVerVariant) implements RandomGeneratorInterface {
            public function __construct(private readonly string $bytes)
            {
            }

            public function generate(int $length): string
            {
                assert(strlen($this->bytes) === $length);
                return $this->bytes;
            }
        });

        return $uuidFactory->uuid4();
    }

    private static function buildSipInputFromV7(string $bytes): string
    {
        $msg = chr(ord($bytes[6]) & 0x0F);
        $msg .= $bytes[7];
        $msg .= chr(ord($bytes[8]) & 0x3F);
        $msg .= substr($bytes, 9, 7);
        return $msg;
    }

    private static function sipHash24(string $key, string $message): string
    {
        $k0 = strrev(substr($key, 0, 8));
        $k1 = strrev(substr($key, 8, 8));
        $key = $k0 . $k1;
        $hash = sodium_crypto_shorthash($message, $key);

        // little-endian to big-endian
        return strrev($hash);
    }
}
