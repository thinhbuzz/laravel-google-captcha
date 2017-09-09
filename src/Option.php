<?php

namespace Buzz\LaravelGoogleCaptcha;

/**
 * @property string lang Language codes (read more here: https://developers.google.com/recaptcha/docs/language)
 * @property bool multiple Set multiple mode
 * @property array attributes Array of captcha attribute
 */
class Option
{

    public function __construct($options = [])
    {
        $this->set($options);
    }

    /**
     * @param array $options
     */
    public function set($options = [])
    {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param string $name
     * @param array $options
     * @return null
     */
    public function get($name, $options = [])
    {
        if (array_key_exists($name, $options)) {
            return $options[$name];
        }
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return null;
    }
}
