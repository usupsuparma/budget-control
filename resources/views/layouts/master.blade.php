<!DOCTYPE html>
<html lang="en">

<meta charset="utf-8" />
<title>@yield('title', ' | FabKin Admin & Dashboards Template')</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta content="Admin & Dashboards Template" name="description" />
<meta content="Pixeleyez" name="author" />

<!-- layout setup -->
<script type="module" src="{{ asset('assets/js/layout-setup.js') }}"></script>

<!-- App favicon -->
<link rel="shortcut icon" href="{{ asset('assets/images/k_favicon_32x.png') }}">

@yield('css')
@include('include.head-css')

<body>

    @include('include.header')
    @include('include.sidebar')
    @include('include.horizontal')

    <main class="app-wrapper">
        <div class="container-fluid">

            @include('include.page-title')

            @yield('content')
        </div>
    </main>
    @include('include.switcher')
    @include('include.scroll-to-top')
    @include('include.footer')

    @include('include.vendor-scripts')

    @yield('js')

</body>

</html>