# Google captcha for Laravel 5, Laravel 6, Laravel 7 and Laravel 8
> Support multiple captcha on page

![Google captcha for Laravel 5, Laravel 6, Laravel 7 and Laravel 8](http://i.imgur.com/aHBOqAS.gif)

> Inspired by [anhskohbo/no-captcha](https://github.com/anhskohbo/no-captcha) and base on [google captcha sdk](https://github.com/google/recaptcha).

## Features

- [x] Multiple captcha on page

- [x] Reset captcha

- [x] Auto discover service provider

- [x] Custom request method

- [x] Using difference key

- [x] Dynamic options on runtime

## Installation

Add the following line to the `require` section of `composer.json`:

```json
{
    "require": {
        "buzz/laravel-google-captcha": "2.*"
    }
}
```

OR

Require this package with composer:
```
composer require buzz/laravel-google-captcha
```

Update your packages with ```composer update``` or install with ```composer install```.

## Setup

> Has support auto discover for Laravel >=5.5

Add ServiceProvider to the `providers` array in `config/app.php`.

```
'Buzz\LaravelGoogleCaptcha\CaptchaServiceProvider',
```

## Publish Config

```
php artisan vendor:publish --provider="Buzz\LaravelGoogleCaptcha\CaptchaServiceProvider"
```

### Custom ReCaptcha request (minimum version 2.1.7)

Edit ``request_method`` in the ``config/captcha.php`` config

file ``config/captcha.php``

```php
<?php
/*
 * Secret key and Site key get on https://www.google.com/recaptcha
 * */
return [
    'secret' => env('CAPTCHA_SECRET', 'default_secret'),
    'sitekey' => env('CAPTCHA_SITEKEY', 'default_sitekey'),
    /**
     * @var string|null Default ``null``.
     * Custom with function name (example customRequestCaptcha) or class@method (example \App\CustomRequestCaptcha@custom).
     * Function must be return instance, read more in repo ``https://github.com/thinhbuzz/laravel-google-captcha-examples``
     */
    'request_method' => null,
    'options' => [
        'multiple' => false,
        'lang' => app()->getLocale(),
    ],
    'attributes' => [
        'theme' => 'light'
    ],
];
```

file ``app/helpers.php``

```php
<?php

function customRequestCaptcha(){
    return new \ReCaptcha\RequestMethod\Post();
}
```

or file ``app/CustomRequestCaptcha.php``

```php
<?php

namespace App;

class CustomRequestCaptcha
{
    public function custom()
    {
        return new \ReCaptcha\RequestMethod\Post();
    }
}
```

## Configuration

Add `CAPTCHA_SECRET` and `CAPTCHA_SITEKEY` to **.env** file:

```
CAPTCHA_SECRET=[secret-key]
CAPTCHA_SITEKEY=[site-key]
```

## Usage

### View example
> Get examples in [examples repo](https://github.com/thinhbuzz/laravel-google-captcha-examples)


### Display reCAPTCHA

```php
{!! app('captcha')->display($attributes) !!}
```

OR use Facade: add `'Captcha' => '\Buzz\LaravelGoogleCaptcha\CaptchaFacade',` to the `aliases` array in `config/app.php` and in template use:

```php
{!! Captcha::display($attributes) !!}
```
OR use Form

```php
{!! Form::captcha($attributes) !!}
```
With custom language support:
```php
{!! app('captcha')->display($attributes = [], $options = ['lang'=> 'vi']) !!}
```

With

```php
// element attributes
$attributes = [
    'data-theme' => 'dark',
    'data-type' => 'audio',
];
```
```php
// package options
$options = [
    'data-theme' => 'dark',
    'data-type'	=> 'audio',
];
```

More information on [google recaptcha document](https://developers.google.com/recaptcha/docs/display)
> Please help me write readme for this content

### Validation

Add `'g-recaptcha-response' => 'required|captcha'` to rules array.

```php
use Validator;
use Illuminate\Support\Facades\Input;

$validate = Validator::make(Input::all(), [
    'g-recaptcha-response' => 'required|captcha'
]);
```

### Testing

When using the Laravel Testing functionality, you will need to mock out the response for the captcha form element.
For any form tests involving the captcha, you can then mock the facade behaviour:

```php
// Prevent validation error on captcha
        CaptchaFacade::shouldReceive('verify')
            ->andReturn(true);
            
// Provide hidden input for your 'required' validation
        CaptchaFacade::shouldReceive('display')
            ->andReturn('<input type="hidden" name="g-recaptcha-response" value="1" />');
            
// Add these when testing multiple captchas on a single page
        CaptchaFacade::shouldReceive('displayJs');
        CaptchaFacade::shouldReceive('displayMultiple');
        CaptchaFacade::shouldReceive('multiple');
```

## Contribute

https://github.com/thinhbuzz/laravel-google-captcha/pulls
