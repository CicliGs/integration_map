<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yandex Reviews</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <div style="padding: 20px; text-align: center; font-family: sans-serif;">
            <p>Загрузка приложения...</p>
        </div>
    </div>
    <noscript>
        <div style="padding: 20px; text-align: center;">
            <h1>Требуется JavaScript</h1>
            <p>Для работы приложения необходимо включить JavaScript в браузере.</p>
        </div>
    </noscript>
</body>
</html>
