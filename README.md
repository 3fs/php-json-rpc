# PHP JSON-RPC

PHP JSON-RPC is a simple JSON-RPC client and server. It is currently in an alpha state, a stable version is expected soon. Word of warning, API will probably change soon as well :)

[![Build Status](https://travis-ci.org/3fs/php-json-rpc.png?branch=master)](https://travis-ci.org/3fs/php-json-rpc)

## Installation

### Composer

Simply add a dependency on `trifs/phpjsonrpc` to your project's `composer.json` file if you use [Composer](http://getcomposer.org/) to manage the dependencies of your project. Here is a minimal example of a `composer.json`:

```php
{
    "require": {
        "trifs/jsonrpc": "dev-master"
    }
}
```

For a system-wide installation via Composer, you can run:

```shell
composer global require 'trifs/phpjsonrpc=*'
```

## Usage Examples

### Client (single request)

```php
$client = new trifs\jsonrpc\Client('http://example.com');
$client->request('method-one')
    ->send();

$client = new trifs\jsonrpc\Client('http://example.com');
$client->notification('method-one')
    ->send();
```

### Client (batch request)

```php
$client = new trifs\jsonrpc\Client('http://example.com');
$client->request('method-one')
    ->request('method-two')
    ->notification('method-three')
    ->send();
```

### Client configuration

Client can be configured with additional options:
 - `timeout` in seconds, connection timeout for `file_get_contents` calls.

```php
$client = new trifs\jsonrpc\Client(
    'http://example.com',
    ['timeout' => 5, ]
);
```

There is also the option of swapping `file_get_contents` call with an implementation that uses `curl`:
```php
$client->setTransporter(new trifs\jsonrpc\Client\Transporter\CurlTransporter());
```
The option might be useful if php.ini configuration `allow_url_fopen` is set to `Off`, which disables `file_get_contents` for remote URLs.


### Server

```php
$input   = file_get_contents('php://input');
$invoker = function($method, array $params = []) {
    return time();
};

$server = new trifs\jsonrpc\Server($input, $invoker);
$server->run();
```

## Contributing

Contributions are always welcome. You make our lives easier by sending us your contributions through GitHub pull requests.

Due to time constraints, we are not always able to respond as quickly as we would like. Please do not take delays personal and feel free to remind us here if you feel that we forgot to respond.

### Using PHP JSON-RPC in a development environment

To set PHP JSON-RPC up locally, make sure to have [Vagrant](http://vagrantup.com) and [VirtualBox](http://virtualbox.org) installed.

```shell
git clone git://github.com/3fs/php-json-rpc
cd php-json-rpc
vagrant up
```

After making the changes, run `./build/qa.sh all`, sit back and relax. If there are problems reported, repeat. If not, try harder :)
