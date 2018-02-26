<?php

namespace App;

class CustomRequestCaptcha
{
    public function custom()
    {
        return new \ReCaptcha\RequestMethod\Post();
    }
}