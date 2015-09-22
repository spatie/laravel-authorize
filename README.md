# A middleware to check authorization

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-authorize.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-authorize)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-authorize/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-authorize)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/c6adf478-99b9-4a52-8635-881f6b66c8d3.svg?style=flat-square)](https://insight.sensiolabs.com/projects/c6adf478-99b9-4a52-8635-881f6b66c8d3)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-authorize.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-authorize)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-authorize.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-authorize)

This package provides a route middleware to protect routes from unauthorized access. It hooks into the authorization
features that were introduced in Laravel 5.1.11.

Protecting a route can be done by adding middleware to it:
```php
Route::get('/protected-page', [
   'middleware'=> 'userCan:viewProtectedPage',
   'uses' => 'ProtectedPage@index'
]);
```

Of course this middleware can also be applied to a bunch of routes:

```php
Route::group(['prefix' => 'admin', 'middleware' => 'userCan:viewAdmin'], function() {

   //all your controllers of your admin section
   ...
   
});
```

Furthermore the middleware can use route model binding:
```php
Route::get('/article/{article}', [
   'middleware'=> 'userCan:editArticle,article',
   'uses' => 'ArticleController@edit')
]);
```

Spatie is webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Install

You can install the package via composer:
``` bash
$ composer require spatie/laravel-authorize
```

Next, you must register the `\Spatie\Authorize\Middleware\Authorize::class`-routermiddleware:

```php
//app/Http/Kernel.php

protected $routeMiddleware = [
  ...
  'userCan' => \Spatie\Authorize\Middleware\Authorize::class,
];
```

## Usage
Coming soon

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

A big thank you to [Joseph Silber](https://github.com/JosephSilber) for all the excellent feedback he gave
while this package was being created.

## About Spatie
Spatie is webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
