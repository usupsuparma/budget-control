<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-layout="vertical">

<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? 'Budget Control' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

    <!-- Layout setup -->
    <script type="module" src="{{ asset('assets/js/layout-setup.js') }}"></script>

    <link rel="shortcut icon" href="{{ asset('assets/images/k_favicon_32x.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}">
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet">

    @livewireStyles

    <!-- Background FULLSCREEN -->
</head>

<body>
    {{ $slot }}

    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/scroll-top.init.js') }}"></script>

    @livewireScripts
    <script src="//unpkg.com/alpinejs" defer></script>

</body>

</html>