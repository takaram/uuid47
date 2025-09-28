<?php

namespace Takaram\Uuid47;

use InvalidArgumentException;
use function sodium_crypto_shorthash;
use function strlen;
use function strrev;
use function substr;

/**
 * @internal
 */
class SipHash24
{
    private string $key;

    public function __construct(string $key)
    {
        if (strlen($key) !== 16) {
            throw new InvalidArgumentException('Key must be 16 bytes long');
        }

        // convert to little-endian
        $k0 = strrev(substr($key, 0, 8));
        $k1 = strrev(substr($key, 8, 8));
        $this->key = $k0 . $k1;
    }

    public function hash(string $message): string
    {
        $hash = sodium_crypto_shorthash($message, $this->key);

        // little-endian to big-endian
        return strrev($hash);
    }

}
