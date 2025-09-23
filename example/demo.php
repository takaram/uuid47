<?php
declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Takaram\Uuid47\Uuid47;

require __DIR__ . '/../vendor/autoload.php';

// Example: parse a v7 from DB, emit façade, then decode back.
$key = hex2bin('0123456789abcdeffedcba9876543210');

// Example v7 string (any valid v7 will do):
$uuid = Uuid::fromString('018f2d9f-9a2a-7def-8c3f-7b1a2c4d5e6f');
$facade = Uuid47::encode($uuid, $key);
$back = Uuid47::decode($facade, $key);

echo "v7 in : $uuid\n";
echo "v4 out: $facade\n";
echo "back  : $back\n";
