<?php
declare(strict_types=1);

namespace Takaram\Uuid47;

use InvalidArgumentException;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;
use function chr;
use function sodium_crypto_shorthash;
use function strlen;
use function strrev;
use function substr;

final class Uuid47
{
    public static function encode(UuidInterface $v7, string $key): UuidInterface
    {
        self::validateKeyLength($key);

        $uuidBytes = $v7->getBytes();

        // 1) mask = SipHash24(key, v7.random74bits) -> take low 48 bits
        $sipMsg = self::buildSipInputFromV7($uuidBytes);
        $mask48 = substr(self::sipHash24($key, $sipMsg), 2, 6);

        // 2) encTS = ts ^ mask
        $ts48 = substr($uuidBytes, 0, 6);
        $encTs = $ts48 ^ $mask48;

        // 3) build v4 facade: write encTS, set ver=4, keep rand bytes identical, set variant
        $uuidWithoutVerVariant = $encTs . substr($uuidBytes, 6, 10);
        $newUuidBytes = self::setVersionAndVariant($uuidWithoutVerVariant, 4);

        $uuidFactory = new UuidFactory();
        return $uuidFactory->uuid($newUuidBytes);
    }

    public static function decode(UuidInterface $v4, string $key): UuidInterface
    {
        self::validateKeyLength($key);

        $uuidBytes = $v4->getBytes();

        // 1) rebuild same Sip input from façade (identical bytes)
        $sipMsg = self::buildSipInputFromV7($uuidBytes);
        $mask48 = substr(self::sipHash24($key, $sipMsg), 2, 6);

        // 2) ts = encTS ^ mask
        $encTs = substr($uuidBytes, 0, 6);
        $ts48 = $encTs ^ $mask48;

        // 3) restore v7: write ts, set ver=7, set variant
        $uuidWithoutVerVariant = $ts48 . substr($uuidBytes, 6, 10);
        $newUuidBytes = self::setVersionAndVariant($uuidWithoutVerVariant, 7);

        $uuidFactory = new UuidFactory();
        return $uuidFactory->uuid($newUuidBytes);
    }

    private static function buildSipInputFromV7(string $bytes): string
    {
        $msg = $bytes[6] & "\x0F";
        $msg .= $bytes[7];
        $msg .= $bytes[8] & "\x3F";
        $msg .= substr($bytes, 9, 7);
        return $msg;
    }

    private static function sipHash24(string $key, string $message): string
    {
        // convert to little-endian
        $k0 = strrev(substr($key, 0, 8));
        $k1 = strrev(substr($key, 8, 8));
        $key = $k0 . $k1;
        $hash = sodium_crypto_shorthash($message, $key);

        // little-endian to big-endian
        return strrev($hash);
    }

    private static function setVersionAndVariant(string $bytes, int $version): string
    {
        // Set version
        $bytes[6] = ($bytes[6] & "\x0F") | chr($version << 4);
        // Set variant (RFC 4122)
        $bytes[8] = ($bytes[8] & "\x3F") | "\x80";
        return $bytes;
    }

    private static function validateKeyLength(string $key): void
    {
        if (strlen($key) !== 16) {
            throw new InvalidArgumentException('Key must be 16 bytes long');
        }
    }
}
