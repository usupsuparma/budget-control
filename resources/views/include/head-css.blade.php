<!-- Simplebar Css -->
<link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}">
<!-- Swiper Css -->
<link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
<!-- Nouislider Css -->
<link href="{{ asset('assets/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
<!-- Bootstrap Css -->
<link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
<!--datatable css-->
<link rel="stylesheet" href="{{ asset('assets/libs/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}" />
<!--datatable responsive css-->
<link rel="stylesheet" href="{{ asset('assets/libs/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}" />
<!--sweet alert css-->
<link rel="stylesheet" href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" />
<!--icons css-->
<link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
<!-- App Css-->
<link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">

<link rel="stylesheet" href="{{ asset('assets/css/button.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/table-compact.css') }}">

<style>
    /* ================================
   SIDEBAR ACTIVE & HOVER COLOR
   ================================ */

    /* Link utama (Dashboard, KPI, dll) */
    .pe-app-sidebar .pe-nav-link.active,
    .pe-app-sidebar .pe-nav-link:hover {
        color: #0099d6 !important;
    }

    /* Icon utama */
    .pe-app-sidebar .pe-nav-link.active .pe-nav-icon,
    .pe-app-sidebar .pe-nav-link:hover .pe-nav-icon {
        color: #0099d6 !important;
    }

    /* Arrow dropdown */
    .pe-app-sidebar .pe-nav-link.active .pe-nav-arrow,
    .pe-app-sidebar .pe-nav-link:hover .pe-nav-arrow {
        color: #0099d6 !important;
    }

    /* ================================
   SUBMENU (Division, Department, dll)
   ================================ */
    .pe-app-sidebar .pe-slide-item .pe-nav-link.active,
    .pe-app-sidebar .pe-slide-item .pe-nav-link:hover {
        color: #0099d6 !important;
    }

    /* Icon submenu */
    .pe-app-sidebar .pe-slide-item .pe-nav-link.active i,
    .pe-app-sidebar .pe-slide-item .pe-nav-link:hover i {
        color: #0099d6 !important;
    }

    /* ================================
   GARIS & BULLET SUBMENU
   ================================ */
    .pe-app-sidebar .pe-slide-menu::before {
        background-color: #0099d6 !important;
    }

    .pe-app-sidebar .pe-slide-item::before {
        background-color: #0099d6 !important;
    }

    /* Optional: dot indicator */
    .pe-app-sidebar .pe-slide-item.active::before {
        background-color: #0099d6 !important;
    }

    /* ================================
   DATATABLE PAGINATION COLOR
   ================================ */

    /* Normal */
    .page-link {
        color: #0099d6 !important;
        border-color: #0099d6 !important;
    }

    /* Hover */
    .page-link:hover {
        color: #ffffff !important;
        background-color: #0099d6 !important;
        border-color: #0099d6 !important;
    }

    /* Active page */
    .page-item.active .page-link {
        background-color: #0099d6 !important;
        border-color: #0099d6 !important;
        color: #ffffff !important;
    }

    /* Disabled (<<, <, >, >> saat tidak aktif) */
    .page-item.disabled .page-link {
        color: #b5dff1 !important;
        border-color: #e1f2f9 !important;
        background-color: #ffffff !important;
    }
</style>