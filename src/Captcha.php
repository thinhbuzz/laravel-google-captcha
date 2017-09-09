<?php

namespace Buzz\LaravelGoogleCaptcha;

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
     * Use this for communication between your site and Google. Be sure to keep it a secret.
     *
     * @var string $secret
     */
    protected $secret;

    /**
     * Use this in the HTML code your site serves to users.
     *
     * @var string $siteKey
     */
    protected $siteKey;

    /**
     * @param string $secret
     * @param string $siteKey
     */
    public function __construct($secret, $siteKey)
    {
        $this->options = new Option(['multiple' => false]);
        $this->secret = $secret;
        $this->siteKey = $siteKey;
    }

    /**
     * Create captcha element with attributes
     *
     * @param  array $attributes
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
        if (!empty($this->options->get('attributes', $options))) {
            $attributes = array_merge($this->options->get('attributes', $options), $attributes);
        }
        if ($isMultiple) {
            array_push($this->captchaAttributes, $attributes);
        } else {
            $attributes['data-sitekey'] = $this->siteKey;
        }

        return $html . '<div class="g-recaptcha"' . $this->buildAttributes($attributes) . '></div>';
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
        $excludeAttributes = ['id'];
        $html = ' <script type="text/javascript">var ' . $this->callbackName . ' = function() {';
        foreach ($this->captchaAttributes as $index => $captchaAttribute) {
            $attributes = ['sitekey' => $this->siteKey];
            foreach ($captchaAttribute as $key => $value) {
                if (in_array($key, $excludeAttributes)) {
                    continue;
                }
                if (strpos($key, 'data-') === false) {
                    $attributes[$key] = $value;
                } else {
                    $attributes[str_replace('data-', '', $key)] = $value;
                }
            }
            $html .= 'grecaptcha.render(\'' . $captchaAttribute['id'] . '\', ' . json_encode($attributes) . ');';
        }
        $html .= '};</script>';

        return $html;
    }

    /**
     * @param array $options
     * @param array $attributes
     * @return string
     * @internal param null $lang
     */
    public function displayJs($options = [], $attributes = ['async', 'defer'])
    {
        return '<script src="' . $this->getJsLink($options) . '" ' . implode(' ', $attributes) . '></script>';
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
     * @param boolean $multiple
     */
    public function multiple($multiple = true)
    {
        $this->options->multiple = $multiple;
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
     *
     * @return bool
     */
    public function verify($response, $clientIp = null)
    {
        if (empty($response)) return false;
        $resp = (new ReCaptcha($this->secret))->verify($response, $clientIp);

        return $resp->isSuccess();
    }
}
