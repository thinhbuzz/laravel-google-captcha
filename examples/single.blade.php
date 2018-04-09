<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
</head>
<body>
<form>
    {!! app('captcha')->display() !!}
</form>
<button id="reset">Reset</button>
<script>
  var reset = document.querySelector('#reset');
  if (reset) {
    reset.addEventListener('click', () => {
      grecaptcha.reset()
    });
  }
</script>
</body>
</html>
