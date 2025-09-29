# UUIDv47 for PHP

[![Packagist Version](https://img.shields.io/packagist/v/takaram/uuid47)](https://packagist.org/packages/takaram/uuid47)
[![Packagist Downloads](https://img.shields.io/packagist/dt/takaram/uuid47)](https://packagist.org/packages/takaram/uuid47/stats)
[![CI](https://img.shields.io/github/check-runs/takaram/uuid47/main)](https://github.com/takaram/uuid47/actions/workflows/ci.yaml)
[![License: MIT](https://img.shields.io/packagist/l/takaram/uuid47)](https://opensource.org/licenses/MIT)

This is a PHP implementation of [UUIDv47](https://uuidv47.stateless.me/).

UUIDv47 allows you to encode a UUIDv7 into a UUIDv4 and back again.
Store UUIDv7 in a database, which is timestamp-based and sortable, while showing it to users as a random-looking UUIDv4.

## Installation

Install the package with Composer:

```bash
composer require takaram/uuid47
```

## Usage

Here is a simple example of how to use this library:

```php
<?php
declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Takaram\Uuid47\Uuid47;

require __DIR__ . '/vendor/autoload.php';

// Example: parse a v7 from DB, emit façade, then decode back.
$key = hex2bin('0123456789abcdeffedcba9876543210');

// Example v7 string (any valid v7 will do):
$uuid = Uuid::fromString('018f2d9f-9a2a-7def-8c3f-7b1a2c4d5e6f');
$facade = Uuid47::encode($uuid, $key);
$back = Uuid47::decode($facade, $key);

echo "v7 in : $uuid\n";
echo "v4 out: $facade\n";
echo "back  : $back\n";
```

Alternatively, you can use `Takaram\Uuid47\Codec` class:

```php
<?php
declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Takaram\Uuid47\Codec;

require __DIR__ . '/vendor/autoload.php';

$key = hex2bin('0123456789abcdeffedcba9876543210');
$codec = new Codec($key);

$uuid = Uuid::fromString('018f2d9f-9a2a-7def-8c3f-7b1a2c4d5e6f');
$facade = $codec->encode($uuid, $key);
$back = $codec->decode($facade, $key);

echo "v7 in : $uuid\n";
echo "v4 out: $facade\n";
echo "back  : $back\n";
```

## Testing

To run the tests, you will need to have PHPUnit installed as a dev dependency:

```bash
composer install --dev
./vendor/bin/phpunit
```

## Original Implementation

This project is a port of the original C implementation of UUIDv47, which can be found at https://github.com/stateless-me/uuidv47.

## License

MIT License - see the [LICENSE](./LICENSE) file for details.
