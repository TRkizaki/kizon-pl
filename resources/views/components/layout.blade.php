@props(['title'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · kizon-pl</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <main>
        {{ $slot }}
    </main>
    <footer>
        Data: <a href="https://www.football-data.org">football-data.org</a> · scores may be delayed
    </footer>
</body>
</html>
