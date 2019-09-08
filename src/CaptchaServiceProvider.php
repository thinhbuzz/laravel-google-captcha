<?php

namespace Buzz\LaravelGoogleCaptcha;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class CaptchaServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot()
    {
        $this->bootConfig();
        $this->bootValidator();
    }

    /**
     * Create captcha validator rule
     */
    public function bootValidator()
    {
        /**
         * @var Application $app
         */
        $app = $this->app;
        /**
         * @var Validator $validator
         */
        $validator = $app['validator'];
        $validator->extend('captcha', function ($attribute, $value, $parameters) use ($app) {
            /**
             * @var Captcha $captcha
             */
            $captcha = $app['captcha'];
            /**
             * @var Request $request
             */
            $request = $app['request'];

            return $captcha->verify($value, $request->getClientIp(), $this->mapParameterToOptions($parameters));
        });
        $validator->replacer('captcha', function ($message) {
            return $message === 'validation.captcha' ? 'Failed to validate the captcha.' : $message;
        });
        if ($app->bound('form')) {
            $app['form']->macro('captcha', function ($attributes = []) use ($app) {
                return $app['captcha']->display($attributes, ['lang' => $app->getLocale()]);
            });
        }
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    public function mapParameterToOptions($parameters = [])
    {
        if (!is_array($parameters)) {
            return [];
        }
        $options = [];
        foreach ($parameters as $parameter) {
            $option = explode(':', $parameter);
            if (count($option) === 2) {
                Arr::set($options, $option[0], $option[1]);
            }
        }

        return $options;
    }

    /**
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function bootConfig()
    {
        $path = __DIR__ . '/../config/config.php';
        $this->mergeConfigFrom($path, 'captcha');
        $this->publishes([
            $path => $this->app->make('path.config') . (DIRECTORY_SEPARATOR . 'captcha.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('captcha', function ($app) {
            return new Captcha($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['captcha'];
    }
}
