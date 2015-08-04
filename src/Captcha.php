<?php
namespace Buzz\LaravelGoogleCaptcha;
class Captcha
{

    const CAPTCHA_CLIENT_API = 'https://www.google.com/recaptcha/api.js';

    /**
     * Use this for communication between your site and Google. Be sure to keep it a secret.
     *
     * @var string $secret
     */
    protected $secret;

    /**
     * Use this in the HTML code your site serves to users.
     *
     * @var string $sitekey
     */
    protected $sitekey;

    /**
     * @param string $secret
     * @param string $sitekey
     */
    public function __construct($secret, $sitekey)
    {
        $this->secret  = $secret;
        $this->sitekey = $sitekey;
    }

    /**
     * Create captcha html element
     *
     * @return string
     */
    public function display($attributes = [], $lang = null)
    {
        $attributes['data-sitekey'] = $this->sitekey;
        $html                       = '<script src="' . $this->getJsLink($lang) . '" async defer></script>' . "\n";
        $html .= '<div class="g-recaptcha"' . $this->buildAttributes($attributes) . '></div>';
        return $html;
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
        $recaptcha = new \ReCaptcha\ReCaptcha($this->secret);
        $resp      = $recaptcha->verify($response, $clientIp);
        return $resp->isSuccess();
    }

    /**
     * Create javascript api link with language
     *
     * @return string
     */
    public function getJsLink($lang = null)
    {
        return $lang ? static::CAPTCHA_CLIENT_API . '?hl=' . $lang : static::CAPTCHA_CLIENT_API;
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

}
