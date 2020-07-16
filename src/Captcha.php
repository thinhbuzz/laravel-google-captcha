<?php

namespace Buzz\LaravelGoogleCaptcha;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use ReCaptcha\ReCaptcha;

class Captcha
{
    const CAPTCHA_CLIENT_API = 'https://www.google.com/recaptcha/api.js';

    /**
     * Name of callback function
     *
     * @var string $callbackName
     */
    protected $callbackName = 'buzzNoCaptchaOnLoadCallback';

    /**
     * Name of widget ids
     *
     * @var string $widgetIdName
     */
    protected $widgetIdName = 'buzzNoCaptchaWidgetIds';

    /**
     * Each captcha attributes in multiple mode
     *
     * @var array $multipleCaptchaData
     */
    protected $multipleCaptchaData = [];

    /**
     * @var Repository $config
     */
    protected $config;

    /**
     * @var Repository $app
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['config'];
    }

    /**
     * Create captcha html element
     *
     * @param array $attributes
     * @param array $options
     *
     * @return string
     */
    public function display($attributes = [], $options = [])
    {
        $isMultiple = (bool)$this->optionOrConfig($options, 'options.multiple');
        if (!array_key_exists('id', $attributes)) {
            $attributes['id'] = $this->randomCaptchaId();
        }
        $html = '';
        if (!$isMultiple && Arr::get($attributes, 'add-js', true)) {
            $html .= '<script src="' . $this->getJsLink($options) . '" async defer></script>';
        }
        unset($attributes['add-js']);
        $attributeOptions = $this->optionOrConfig($options, 'attributes');
        if (!empty($attributeOptions)) {
            $attributes = array_merge($attributeOptions, $attributes);
        }
        if ($isMultiple) {
            array_push( $this->multipleCaptchaData, [$attributes, $options] );
        } else {
            $attributes['data-sitekey'] = $this->optionOrConfig($options, 'sitekey');
        }

        return $html . '<div class="g-recaptcha"' . $this->buildAttributes($attributes) . '></div>';
    }

    /**
     * Random id unique
     *
     * @return string
     */
    protected function randomCaptchaId()
    {
        return 'buzzNoCaptchaId_' . md5(uniqid(rand(), true));
    }

    /**
     * Create javascript api link with language
     *
     * @param array $options
     *
     * @return string
     */
    public function getJsLink($options = [])
    {
        $query = [];
        if ($this->optionOrConfig($options, 'options.multiple')) {
            $query = [
                'onload' => $this->callbackName,
                'render' => 'explicit',
            ];
        }
        $lang = $this->optionOrConfig($options, 'options.lang');
        if ($lang) {
            $query['hl'] = $lang;
        }

        return static::CAPTCHA_CLIENT_API . '?' . http_build_query($query);
    }

    /**
     * Create captcha element with attributes
     *
     * @param array $attributes
     *
     * @return string
     */
    protected function buildAttributes(array $attributes)
    {
        $html = [];
        foreach ($attributes as $key => $value) {
            $html[] = $key . '="' . $value . '"';
        }

        return count($html) ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Display multiple captcha on page
     *
     * @param array $globalOptions
     *
     * @return string
     */
    public function displayMultiple($globalOptions = [])
    {
        if (!$this->optionOrConfig($globalOptions, 'options.multiple')) {
            return '';
        }
        $renderHtml = '';
        foreach ($this->multipleCaptchaData as $multipleCaptchaData) {
            $attributes = $multipleCaptchaData[0];
            $options = array_merge( $globalOptions, $multipleCaptchaData[1] );
            $renderHtml .= "{$this->widgetIdName}[\"{$attributes['id']}\"]={$this->buildCaptchaHtml($attributes, $options)}";
        }

        return "<script type=\"text/javascript\">var {$this->widgetIdName}={};var {$this->callbackName}=function(){{$renderHtml}};</script>";
    }

    /**
     * @param array $options
     * @param array $attributes
     *
     * @return string
     * @internal param null $lang
     */
    public function displayJs($options = [], $attributes = ['async', 'defer'])
    {
        return '<script src="' . htmlspecialchars($this->getJsLink($options)) . '" ' . implode(' ', $attributes) . '></script>';
    }

    /**
     * @param boolean $multiple
     */
    public function multiple($multiple = true)
    {
        $this->config->set('captcha.options.multiple', $multiple);
    }

    /**
     * @param array $options
     */
    public function setOptions($options = [])
    {
        $this->config->set('captcha.options', $options);
    }

    /**
     * Verify captcha
     *
     * @param string $response
     * @param string $clientIp
     * @param array $options
     *
     * @return bool
     */
    public function verify($response, $clientIp = null, $options = [])
    {
        if (empty($response)) {
            return false;
        }
        $getRequestMethod = $this->optionOrConfig($options, 'request_method');
        $requestMethod = is_string($getRequestMethod) ? $this->app->call($getRequestMethod) : null;
        $reCaptCha = new ReCaptcha($this->optionOrConfig($options, 'secret'), $requestMethod);

        return $reCaptCha->verify($response, $clientIp)->isSuccess();
    }

    /**
     * Build captcha by attributes
     *
     * @param array $captchaAttribute
     * @param array $options
     *
     * @return string
     */
    protected function buildCaptchaHtml($captchaAttribute = [], $options = [])
    {
        $options = array_merge(
            ['sitekey' => $this->optionOrConfig($options, 'sitekey')],
            $this->optionOrConfig($options, 'attributes', [])
        );
        foreach ($captchaAttribute as $key => $value) {
            $options[str_replace('data-', '', $key)] = $value;
        }
        $options = json_encode($options);

        return "grecaptcha.render('{$captchaAttribute['id']}',{$options});";
    }

    /**
     * @param array $options
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function optionOrConfig($options = [], $key = '', $default = null)
    {
        return Arr::get($options, str_replace('options.', '', $key), $this->config->get('captcha.' . $key, $default));
    }
}
