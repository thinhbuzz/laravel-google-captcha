<!doctype html>
<html lang="{{ app()->getLocale() }}">
{{ app('captcha')->multiple() }}
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
</head>
<body>
<form id="form-1">
    {!! app('captcha')->display() !!}
    {{--    {!! app('captcha')->display([], ['multiple' => true]) !!}--}}
</form>
<form id="form-2">
    {!! app('captcha')->display() !!}
    {{--    {!! app('captcha')->display([], ['multiple' => true]) !!}--}}
</form>
{!! app('captcha')->displayJs() !!}
{!! app('captcha')->displayMultiple() !!}
{{--{!! app('captcha')->displayMultiple(['multiple' => true]) !!}--}}
</body>
</html>
