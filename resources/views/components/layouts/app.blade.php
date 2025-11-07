<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'App' }}</title>
    @livewireStyles
</head>

<body class="bg-gray-100">
    {{ $slot }}
    @livewireScripts
</body>

</html>