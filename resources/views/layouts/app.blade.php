<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') </title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- DataTables -->
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Responsive datatable examples -->
    <link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Sweet Alert -->
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/dropify/css/dropify.min.css') }}" rel="stylesheet">

    <!-- Select2 -->
    <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Datepicker -->
    {{-- <link rel="stylesheet" type="text/css" href="jquery.datetimepicker.css" / > --}}
    <link href="{{ asset('plugins/timepicker/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">

    <!-- Toastr -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">

    <!-- Autocomplete jQuery -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.css"
        rel="stylesheet">

    <!-- App css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/metisMenu.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('checkbox.css') }}" rel="stylesheet" type="text/css" />
    <style>
        :root {
            --active: #9ebdd1e5;

        }

        ul>li.active {
            background-color: var(--active);
            border-radius: 3px;
            box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
        }

        .left-sidenav .topbar-left .logo .logo-lg {
            height: 65px !important;
        }
    </style>
    <style>
        #toast-container>.toast-error {
            background-color: #BD362F;
        }
    </style>
    <style>
        #toast-container>.toast-success {
            background-color: #5cb85c;
        }
    </style>
    <style>
        /* Absolute Center Spinner */

        .loading {
            position: fixed;
            z-index: 99999999999999999;
            height: 2em;
            width: 2em;
            overflow: show;
            margin: auto;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }

        /* Transparent Overlay */
        .loading:before {
            content: '';
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(rgba(20, 20, 20, .8), rgba(0, 0, 0, .8));

            background: -webkit-radial-gradient(rgba(20, 20, 20, .8), rgba(0, 0, 0, .8));
        }

        /* :not(:required) hides these rules from IE9 and below */
        .loading:not(:required) {
            /* hide "loading..." text */
            font: 0/0 a;
            color: transparent;
            text-shadow: none;
            background-color: transparent;
            border: 0;
        }

        .loading:not(:required):after {
            content: '';
            display: block;
            font-size: 10px;
            width: 1em;
            height: 1em;
            margin-top: -0.5em;
            -webkit-animation: spinner 150ms infinite linear;
            -moz-animation: spinner 150ms infinite linear;
            -ms-animation: spinner 150ms infinite linear;
            -o-animation: spinner 150ms infinite linear;
            animation: spinner 150ms infinite linear;
            border-radius: 0.5em;
            -webkit-box-shadow: rgba(255, 255, 255, 0.75) 1.5em 0 0 0, rgba(255, 255, 255, 0.75) 1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) 0 1.5em 0 0, rgba(255, 255, 255, 0.75) -1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) -1.5em 0 0 0, rgba(255, 255, 255, 0.75) -1.1em -1.1em 0 0, rgba(255, 255, 255, 0.75) 0 -1.5em 0 0, rgba(255, 255, 255, 0.75) 1.1em -1.1em 0 0;
            box-shadow: rgba(255, 255, 255, 0.75) 1.5em 0 0 0, rgba(255, 255, 255, 0.75) 1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) 0 1.5em 0 0, rgba(255, 255, 255, 0.75) -1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) -1.5em 0 0 0, rgba(255, 255, 255, 0.75) -1.1em -1.1em 0 0, rgba(255, 255, 255, 0.75) 0 -1.5em 0 0, rgba(255, 255, 255, 0.75) 1.1em -1.1em 0 0;
        }

        /* Animation */

        @-webkit-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @-moz-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @-o-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
    </style>
    <style>
        #loadingg {
            width: 100%;
            height: 100%;
            top: 0px;
            left: 0px;
            position: fixed;
            display: block;
            z-index: 99
        }

        #loading-image {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 100
        }

        .loading-overlay {
            background: rgb(0, 0, 0);
            opacity: 0.5;
            filter: alpha(opacity=50);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999999999999999999999;
        }


        /* hide the page untill #loadingg */
    </style>
    @yield('css')
    @stack('style')
</head>

<body>
    <!-- Left Sidenav -->
    <div class="loading">Loading&#8230;</div>
    <div id="loadingg">
        <img id="loading-image" src="{{ asset('assets/images/Blinking squares.gif') }}" alt="Loading..." />
    </div>
    <div id="remove-overlay" class="loading-overlay"></div>
    <div class="left-sidenav">
        <!-- LOGO -->
        <div class="topbar-left">
            @include('admin.components.logo')
        </div>
        <!--end logo-->
        @include('admin.components.left-nav-header')

        @include('admin.components.nav')
    </div>


    <!-- Top Bar Start -->
    @include('admin.components.header')
    <!-- Top Bar End -->
    <div class="page-wrapper">
        <!-- Page Content-->
        <div class="page-content-tab">

            <div class="container-fluid">
                @yield('content')
                @yield('iframe')
            </div><!-- container -->

            {{-- <footer class="footer text-center text-sm-left">
                <div class="boxed-footer">
                    &copy; {{ date('Y') }} Damian <span
                        class="text-muted d-none d-sm-inline-block float-right">Crafted
                        with <i class="mdi mdi-heart text-danger"></i> by Muhammad Sabir Sabir </span>
                </div>
            </footer> --}}
            <!--end footer-->
        </div>
        <!-- end page content -->
    </div>


    <form action="{{ route('logout') }}" method="POST" id="logout-form">@csrf</form>


    <!-- jQuery  -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/metismenu.min.js') }}"></script>
    <script src="{{ asset('assets/js/waves.js') }}"></script>
    <script src="{{ asset('assets/js/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>

    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('/plugins/dropify/js/dropify.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('plugins/tinymce/tinymce.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <!-- Datepicker  -->
    {{-- <script src="/build/jquery.datetimepicker.full.min.js"></script> --}}
    <script src="{{ asset('plugins/moment/moment.js') }}"></script>
    <script src="{{ asset('plugins/timepicker/bootstrap-material-datetimepicker.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.25.1/trumbowyg.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.25.1/ui/trumbowyg.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
    <script>
        if ($(".simeditor").length > 0) {
            $('.simeditor').trumbowyg();
        }
    </script>
    @if (session('success'))
        <script>
            toastr.success("{{ session('success') }}", {
                timeOut: 10000
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            toastr.error("{{ session('error') }}", {
                timeOut: 10000
            });
        </script>
    @endif


    <script>
        function dispMessage(isError, message, timeout = 10000) {
            try {
                if (isError) {
                    toastr.error(message, {
                        timeOut: timeout
                    });
                } else {
                    toastr.success(message, {
                        timeOut: timeout
                    });
                }

            } catch (error) {
                alert(message);
            }
        }

        $(".dropify").dropify();
        $(".select2").select2({
            width: '100%'
        });
        $(".datepicker").bootstrapMaterialDatePicker({
            weekStart: 0,
            time: false,
            format: 'DD-MM-YYYY',
            minDate: new Date()
        });
        $(".filter").bootstrapMaterialDatePicker({
            weekStart: 0,
            time: false,
            format: 'DD-MM-YYYY',

        });

        tinymce.init({

            selector: '.editor'

        });


        function ghldeleteMsg(url) {
            swal.fire({
                title: "Dont worry!",
                text: "This can be fetched back!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then(function(result) {
                if (result.value) {
                    location.href = url;
                }
            })
        }

        function pDelMsg(url) {
            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!, BUT, Take a note that,  This will only delete it from your database and not from Ghl ",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then(function(result) {
                if (result.value) {
                    location.href = url;
                }
            })
        }

        function deleteMsg(url) {
            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then(function(result) {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
    <script>
        window.onload = function() {
            document.getElementById("loadingg").style.display = "none"
            $('#maps').hide();
            $('#remove-overlay').removeClass('loading-overlay');
            //show maps after 2 seconds
            setTimeout(function() {
                $('#maps').show();
            }, 2000);

        }

        $(document).on('click', '.btn-close', function(e) {
            e.preventDefault();

            $(this).closest('.modal').modal('hide');
        })
    </script>
    @yield('js')
    @stack('script')

</body>

</html>
