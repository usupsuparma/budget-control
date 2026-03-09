<!-- JAVASCRIPT -->
<!-- jQuery -->
<script src="{{ asset('assets/libs/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/layout-setup.js') }}"></script>
<script src="{{ asset('assets/js/scroll-top.init.js') }}"></script>
<script src="{{ asset('assets/libs/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // When any submenu is about to show, hide all other submenus.
        $('.pe-slide-menu.collapse').on('show.bs.collapse', function() {
            $('.pe-slide-menu.collapse.show').not(this).collapse('hide');
        });

        // On page load, if multiple submenus are marked 'show', collapse all but the first one.
        var shown = $('.pe-slide-menu.collapse.show');
        if (shown.length > 1) {
            shown.not(shown.first()).collapse('hide');
        }
    });
</script>
