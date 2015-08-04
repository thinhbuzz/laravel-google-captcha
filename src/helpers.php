<?php
if (!function_exists('captcha_html')) {
    function captcha_html($attributes = [], $lang = null)
    {
        if (is_null($lang)) $lang = config('app.locale');
        return app('captcha')->display($attributes, $lang);
    }
}