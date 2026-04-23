# Blueticks PHP SDK

Official PHP SDK for the [Blueticks](https://blueticks.co) API.

## Install

```bash
composer require blueticks/blueticks
```

You also need a PSR-18 HTTP client. Any of these will do:

```bash
composer require guzzlehttp/guzzle      # most common
# or
composer require symfony/http-client
# or
composer require php-http/curl-client php-http/message
```

## Quickstart

```php
<?php

require 'vendor/autoload.php';

use Blueticks\Blueticks;

$client = new Blueticks(['apiKey' => getenv('BLUETICKS_API_KEY')]);

$account = $client->account->retrieve();

echo $account->name;
```

## Supported PHP versions

- PHP 8.1, 8.2, 8.3

## License

MIT
