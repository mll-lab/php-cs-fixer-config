# php-cs-fixer-config

Shared configuration for php-cs-fixer

[![GitHub license](https://img.shields.io/github/license/mll-lab/php-cs-fixer-config.svg)](https://github.com/mll-lab/php-cs-fixer-config/blob/master/LICENSE)
[![Packagist](https://img.shields.io/packagist/v/mll-lab/php-cs-fixer-config.svg)](https://packagist.org/packages/mll-lab/php-cs-fixer-config)
[![Packagist](https://img.shields.io/packagist/dt/mll-lab/php-cs-fixer-config.svg)](https://packagist.org/packages/mll-lab/php-cs-fixer-config)

## Installation

    composer require --dev mll-lab/php-cs-fixer-config

## Usage

In your `.php_cs`:

```php
<?php declare(strict_types=1);

use function MLL\PhpCsFixerConfig\config;

$finder = PhpCsFixer\Finder::create()
    ->notPath('vendor')
    ->in(__DIR__)
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return config($finder);
```

Enable risky:

```php
use function MLL\PhpCsFixerConfig\risky;

return risky($finder);
```

Override rules:

```php
return config($finder, [
    'some_rule' => false
]);
```

Customize config:

```php
return config($finder)
    ->setHideProgress(true);
```
