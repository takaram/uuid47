<?php
declare(strict_types=1);

namespace Takaram\Uuid47;

use InvalidArgumentException;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;
use function chr;
use function strlen;
use function substr;

class Codec
{
    private SipHash24 $sipHash24;

    public function __construct(string $key)
    {
        if (strlen($key) !== 16) {
            throw new InvalidArgumentException('Key must be 16 bytes long');
        }

        $this->sipHash24 = new SipHash24($key);
    }

    /**
     * Encodes a UUIDv7 into a UUIDv4.
     *
     * The timestamp from the UUIDv7 is encrypted using SipHash-2-4.
     * The resulting UUID is a UUIDv4 that contains the encrypted timestamp.
     *
     * @param UuidInterface $v7 The UUIDv7 to encode.
     * @return UuidInterface The encoded UUIDv4.
     */
    public function encode(UuidInterface $v7): UuidInterface
    {
        $uuidBytes = $v7->getBytes();

        // 1) mask = SipHash24(key, v7.random74bits) -> take low 48 bits
        $sipMsg = $this->buildSipInputFromV7($uuidBytes);
        $mask48 = substr($this->sipHash24->hash($sipMsg), 2, 6);

        // 2) encTS = ts ^ mask
        $ts48 = substr($uuidBytes, 0, 6);
        $encTs = $ts48 ^ $mask48;

        // 3) build v4 facade: write encTS, set ver=4, keep rand bytes identical, set variant
        $uuidWithoutVerVariant = $encTs . substr($uuidBytes, 6, 10);
        $newUuidBytes = $this->setVersionAndVariant($uuidWithoutVerVariant, 4);

        $uuidFactory = new UuidFactory();
        return $uuidFactory->uuid($newUuidBytes);
    }

    /**
     * Decodes a UUIDv4 back into a UUIDv7.
     *
     * It decrypts the timestamp and restores the UUIDv7.
     *
     * @param UuidInterface $v4 The UUIDv4 to decode.
     * @return UuidInterface The decoded UUIDv7.
     */
    public function decode(UuidInterface $v4): UuidInterface
    {
        $uuidBytes = $v4->getBytes();

        // 1) rebuild same Sip input from façade (identical bytes)
        $sipMsg = $this->buildSipInputFromV7($uuidBytes);
        $mask48 = substr($this->sipHash24->hash($sipMsg), 2, 6);

        // 2) ts = encTS ^ mask
        $encTs = substr($uuidBytes, 0, 6);
        $ts48 = $encTs ^ $mask48;

        // 3) restore v7: write ts, set ver=7, set variant
        $uuidWithoutVerVariant = $ts48 . substr($uuidBytes, 6, 10);
        $newUuidBytes = $this->setVersionAndVariant($uuidWithoutVerVariant, 7);

        $uuidFactory = new UuidFactory();
        return $uuidFactory->uuid($newUuidBytes);
    }

    private function buildSipInputFromV7(string $bytes): string
    {
        $msg = $bytes[6] & "\x0F";
        $msg .= $bytes[7];
        $msg .= $bytes[8] & "\x3F";
        $msg .= substr($bytes, 9, 7);
        return $msg;
    }

    private function setVersionAndVariant(string $bytes, int $version): string
    {
        // Set version
        $bytes[6] = ($bytes[6] & "\x0F") | chr($version << 4);
        // Set variant (RFC 4122)
        $bytes[8] = ($bytes[8] & "\x3F") | "\x80";
        return $bytes;
    }

}
