# Google captcha for laravel 5.*

![google captcha for laravel 5](http://i.imgur.com/aHBOqAS.gif)

> Inspired by [anhskohbo/no-captcha](https://github.com/anhskohbo/no-captcha) and base on [google captcha sdk](https://github.com/google/recaptcha).

## Installation

Add the following line to the `require` section of `composer.json`:

```json
{
    "require": {
        "buzz/laravel-google-captcha": "1.*"
    }
}
```

OR

Require this package with composer:
```
composer require buzz/laravel-google-captcha
```

Update your packages with ```composer update``` or install with ```composer install```.

## Laravel 5

### Setup

Add ServiceProvider to the `providers` array in `app/config/app.php`.

```
'Buzz\LaravelGoogleCaptcha\CaptchaServiceProvider',
```

### Configuration

Add `CAPTCHA_SECRET` and `CAPTCHA_SITEKEY` to **.env** file:

```
CAPTCHA_SECRET=[secret-key]
CAPTCHA_SITEKEY=[site-key]
```

### Usage

##### Display reCAPTCHA

```php
{!! app('captcha')->display($attributes) !!}
```

OR

```php
{!! captcha_html($attributes) !!}
```

OR use Facade: add `'Captcha' => '\Buzz\LaravelGoogleCaptcha\CaptchaFacade',` to the `aliases` array in `app/config/app.php` and in template use:

```php
{!! Captcha::display($attributes) !!}
```
OR use Form

```php
{!! Form::captcha($attributes) !!}
```

With

```php
$attributes = [
	'data-theme' => 'dark',
	'data-type'	=>	'audio',
];
```

More infomation on [google recaptcha document](https://developers.google.com/recaptcha/docs/display)

##### Validation

Add `'g-recaptcha-response' => 'required|captcha'` to rules array.

```php
$validate = Validator::make(Input::all(), [
	'g-recaptcha-response' => 'required|captcha'
]);
```


## Contribute

https://github.com/thinhbuzz/laravel-google-captcha/pulls