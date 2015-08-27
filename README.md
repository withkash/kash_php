## Installation

Include via Composer:
```js
    "require": {
        "kash/kash-php": "*"
    }
```

## Documentation

Documentation is available at [http://docs.withkash.com](http://docs.withkash.com).

## Tutorial

Each function call returns a promise.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Kash\Kash;

Kash::setApiKey('sk_test_b746ab1e46e0d0fff0f62912ede52c52');
$customerId = "dce5d71e-0ba5-4114-b9d2-9644056a77ce";
$result = Kash::authorizeAmount($customerId, 2000);
print($result->authorization_id);
```

