<?php

function customRequestCaptcha(){
    return new \ReCaptcha\RequestMethod\Post();
}