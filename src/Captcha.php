<?php

namespace Buzz\LaravelGoogleCaptcha;

use Illuminate\Contracts\Foundation\Application;
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
     * @var array $captchaAttributes
     */
    protected $captchaAttributes = [];

    /**
     * Set global options
     *
     * @var Option $options
     */
    protected $options;

    /**
     * @var \Illuminate\Contracts\Config\Repository $config
     */
    protected $config;

    /**
     * @var \Illuminate\Contracts\Config\Repository $app
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['config'];
        $this->options = new Option($this->config->get('captcha.options'));
    }

    /**
     * Create captcha html element
     *
     * @param array $attributes
     * @param array $options
     * @return string
     */
    public function display($attributes = [], $options = [])
    {
        $isMultiple = (bool)$this->options->get('multiple', $options);
        if (!array_key_exists('id', $attributes)) {
            $attributes['id'] = $this->randomCaptchaId();
        }
        $html = '';
        if (!$isMultiple && array_get($attributes, 'add-js', true)) {
            $html .= '<script src="' . $this->getJsLink($options) . '" async defer></script>';
        }
        unset($attributes['add-js']);
        $attributeOptions = $this->options->get('attributes', $options);
        if (!empty($attributeOptions)) {
            $attributes = array_merge($attributeOptions, $attributes);
        }
        if ($isMultiple) {
            array_push($this->captchaAttributes, $attributes);
        } else {
            $attributes['data-sitekey'] = $this->config->get('captcha.sitekey');
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
     * @return string
     */
    public function getJsLink($options = [])
    {
        $query = [];
        if ($this->options->get('multiple', $options)) {
            $query = [
                'onload' => $this->callbackName,
                'render' => 'explicit',
            ];
        }
        $lang = $this->options->get('lang', $options);
        if ($lang) {
            $query['hl'] = $lang;
        }

        return static::CAPTCHA_CLIENT_API . '?' . http_build_query($query);
    }

    /**
     * Create captcha element with attributes
     *
     * @param  array $attributes
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
     * @param array $options
     * @return string
     */
    public function displayMultiple($options = [])
    {
        if (!$this->options->get('multiple', $options)) {
            return '';
        }
        $renderHtml = '';
        foreach ($this->captchaAttributes as $captchaAttribute){
            $renderHtml .= "{$this->widgetIdName}[\"{$captchaAttribute['id']}\"]={$this->buildCaptchaHtml($captchaAttribute)}";
        }

        return "<script type=\"text/javascript\">var {$this->widgetIdName}={};var {$this->callbackName}=function(){{$renderHtml}};</script>";
    }

    /**
     * @param array $options
     * @param array $attributes
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
        $this->options->multiple = $multiple;
    }

    /**
     * @param array $options
     */
    public function setOptions($options = [])
    {
        $this->options->set($options);
    }

    /**
     * Verify captcha
     *
     * @param  string $response
     * @param  string $clientIp
     * @return bool
     */
    public function verify($response, $clientIp = null)
    {
        if (empty($response)) {
            return false;
        }
        $getRequestMethod = $this->config->get('captcha.request_method');
        $requestMethod = is_string($getRequestMethod) ? $this->app->call($getRequestMethod) : null;
        $reCaptCha = new ReCaptcha($this->config->get('captcha.secret'), $requestMethod);

        return $reCaptCha->verify($response, $clientIp)->isSuccess();
    }

    /**
     * Build captcha by attributes
     *
     * @param array $captchaAttribute
     *
     * @return string
     */
    protected function buildCaptchaHtml(array $captchaAttribute)
    {
        $options = array_merge(
            ['sitekey' => $this->config->get('captcha.sitekey')],
            $this->config->get('captcha.attributes', [])
        );
        foreach ($captchaAttribute as $key => $value) {
            $options[str_replace('data-', '', $key)] = $value;
        }
        $options = json_encode($options);
        return "grecaptcha.render('{$captchaAttribute['id']}',{$options});";
    }
}
