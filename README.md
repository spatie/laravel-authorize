# A middleware to check authorization

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-authorize.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-authorize)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-authorize/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-authorize)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/c6adf478-99b9-4a52-8635-881f6b66c8d3.svg?style=flat-square)](https://insight.sensiolabs.com/projects/c6adf478-99b9-4a52-8635-881f6b66c8d3)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-authorize.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-authorize)
[![StyleCI](https://styleci.io/repos/42896120/shield?branch=master)](https://styleci.io/repos/42896120)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-authorize.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-authorize)

This package provides a route middleware to protect routes from unauthorized access. It hooks into the authorization
features that were [introduced in Laravel 5.1.11](http://laravel.com/docs/5.1/authorization).

Protecting a route can be done by adding middleware to it:
```php
Route::get('/top-secret-page', [
   'middleware' => 'can:viewTopSecretPage',
   'uses' => 'TopSecretController@index',
]);
```

Of course this middleware can also be applied to a bunch of routes:

```php
Route::group(['prefix' => 'admin', 'middleware' => 'can:viewAdmin'], function() {

   //all the controllers of your admin section
   ...
   
});
```

Furthermore the middleware can use [route model binding](https://laracasts.com/series/laravel-5-fundamentals/episodes/18):
```php
Route::get('/post/{post}', [
   'middleware' => 'can:editPost,post',
   'uses' => 'PostController@edit',
]);
```

Spatie is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment you are required to send us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

The best postcards will get published on the open source page on our website.

## Do not use in Laravel 5.2.28 and up

Laravel 5.2.28 or higher contain the middleware this package provides [out of the box](https://github.com/laravel/framework/blob/v5.2.28/src/Illuminate/Foundation/Http/Middleware/Authorize.php). There's no need do install this package in those versions of Laravel. 

## Install

You can install the package via composer:
``` bash
$ composer require spatie/laravel-authorize
```

Next, you must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    Spatie\Authorize\AuthorizeServiceProvider::class,
];
```

Next, the `\Spatie\Authorize\Middleware\Authorize::class`-middleware must be registered in the kernel:

```php
//app/Http/Kernel.php

protected $routeMiddleware = [
  ...
  'can' => \Spatie\Authorize\Middleware\Authorize::class,
];
```

Naming the middleware `can` is just a suggestion. You can give it any name you'd like.

The `authorize`-middleware includes all functionality provided by the standard `auth`-middleware. So you could
also opt to replace the `App\Http\Middleware\Authenticate`-middleware by `Spatie\Authorize\Middleware\Authorize`:

```php
//app/Http/Kernel.php

protected $routeMiddleware = [
    'auth' => 'Spatie\Authorize\Middleware\Authorize',
    ...
];
```

You can publish the config-file with:
```bash
php artisan vendor:publish --provider="Spatie\Authorize\AuthorizeServiceProvider"
```

This is the contents of the published config file:

```php
return [
    /*
     * The path to redirect for login.
     */
    'login_url' => 'auth/login'
];
```

## Usage

### Checking authentication
When the middleware is used without any parameters at all, it will only allow logged in users to use the route.
If you plan on using the middleware like this I recommend that you replace the standard `auth`-middleware with the one
provided by this package. 

```php
//only logged in users will be able to see this

Route::get('/top-secret-page', ['middleware' => 'auth', 'uses' => 'TopSecretController@index']);
```

### Checking authorization
The middleware accepts the name of an ability you have defined as the first parameter:

```php
//only users with the viewTopSecretPage-ability be able to see this

Route::get('/top-secret-page', [
   'middleware' => 'can:viewTopSecretPage',
   'uses' => 'TopSecretController@index',
]);
```

### Using form model binding
Image you've set up an ability like this:

```php
//inside the boot method of AuthServiceProvider

$gate->define('update-post', function ($user, $post) {
    return $user->id === $post->user_id;
});
```

The middleware accepts the name of a bound model as the second parameter.

```php
Route::get('/post/{post}', [
   'middleware' => 'can:editPost,post',
   'uses' => 'PostController@edit',
]);
```

Behind the scene the middleware will pass the model bound that is bound to the round to
the defined `update-post`-ability.

## What happens with unauthorized requests?

### Default behaviour

This is the default behaviour defined in the middleware.

```php
use Symfony\Component\HttpKernel\Exception\HttpException;
...

protected function handleUnauthorizedRequest($request, $ability = null, $model = null)
{
    if ($request->ajax()) {
        return response('Unauthorized.', Response::HTTP_UNAUTHORIZED);
    }

    if (!$request->user()) {
        return redirect()->guest(config('laravel-authorize.login_url'));
    }

    throw new HttpException(Response::HTTP_UNAUTHORIZED, 'This action is unauthorized.');
}
```

So guests will get redirected to the default login page, logged in users will get a response
with status `HTTP_UNAUTHORIZED` aka 401.

### Custom behaviour

To customize the default behaviour you can easily extend the default middleware and
override the `handleUnauthorizedRequest`-method. Don't forget to register your class at the kernel.

If you would like to let all unauthorized users know that you are actually a teapot you can do so.

```php
//app/Http/Middleware/Authorize.php

namespace App\Http\Middleware;

use Spatie\Authorize\Middleware\Authorize as BaseAuthorize;
use Symfony\Component\HttpFoundation\Response;

class Authorize extends BaseAuthorize
{
    protected function handleUnauthorizedRequest($request, $ability = null, $model = null)
    {
        return reponse('I am a teapot.', Response::HTTP_I_AM_A_TEAPOT);
    }
}
```

In the kernel:

```php
//app/Http/Kernel.php

  protected $routeMiddleware = [
        'can' => 'App\Http\Middleware\Authorize',
        ...
    ];
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

This package contains integration tests that are powered by [orchestral/testbench](https://github.com/orchestral/testbench).

You can run all tests with:
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
