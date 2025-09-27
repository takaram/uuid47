<?php
declare(strict_types=1);

namespace Takaram\Uuid47;

use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

final class Uuid47
{
    /**
     * Encodes a UUIDv7 into a UUIDv4, using a secret key.
     *
     * This method takes a UUIDv7 and a 16-byte secret key to produce a UUIDv4.
     * The timestamp from the UUIDv7 is encrypted using SipHash-2-4 with the provided key.
     * The resulting UUID is a UUIDv4 that contains the encrypted timestamp.
     *
     * @param UuidInterface $v7 The UUIDv7 to encode.
     * @param string $key A 16-byte secret key.
     * @return UuidInterface The encoded UUIDv4.
     * @throws InvalidArgumentException If the key is not 16 bytes long.
     */
    public static function encode(UuidInterface $v7, string $key): UuidInterface
    {
        return (new Codec($key))->encode($v7);
    }

    /**
     * Decodes a UUIDv4 back into a UUIDv7, using the same secret key.
     *
     * This method takes a UUIDv4 (that was previously encoded with the `encode` method)
     * and the same 16-byte secret key to restore the original UUIDv7.
     * It decrypts the timestamp and restores the UUIDv7.
     *
     * @param UuidInterface $v4 The UUIDv4 to decode.
     * @param string $key The same 16-byte secret key used for encoding.
     * @return UuidInterface The decoded UUIDv7.
     * @throws InvalidArgumentException If the key is not 16 bytes long.
     */
    public static function decode(UuidInterface $v4, string $key): UuidInterface
    {
        return (new Codec($key))->decode($v4);
    }

}
