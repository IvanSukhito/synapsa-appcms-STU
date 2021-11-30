<?php
    $clinicThemesColor = session()->get('admin_clinic_themes_color');
    $clinicLogo = session()->get('admin_clinic_logo');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    @section('head')
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
    @show

    <title>{{ env('WEBSITE_NAME') }} | @yield('title')</title>

    @section('css')
            <link rel="stylesheet" href="{{ asset('/assets/cms/css/app.css') }}">
            <link rel="stylesheet" href="{{ asset('/assets/cms/js/calendar/fullcalendar.css') }}">
            <link rel="stylesheet" href="{{ asset('/assets/cms/dropify/css/dropify.min.css') }}">
            <link rel="stylesheet" href="{{ asset('/assets/cms/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css') }}">
    @show
    @section('script-top')
    @show
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light" style="background-color:{{ $clinicThemesColor }}">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>
            </li>
            <li class="nav-item">
                <a class="nav-link">{{ session('admin_clinic_name') }}</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('admin.profile.index') }}" class="nav-link">{{ session('admin_name')  }}</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('admin.logout') }}" class="nav-link">@lang('general.sign_out')</a>
            </li>
        </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('admin') }}" class="brand-link logo-switch">
            <span class="brand-image-xl logo-xs">
                @if(strlen($clinicLogo) > 0)
                    <img src="{{ env('OSS_URL').'/'.$clinicLogo }}" class="img-responsive" style="max-height: 40px;" alt="logo"/>
                @else
                    {{ substr(env('WEBSITE_NAME'), 0, 2) }}
                @endif
            </span>
            <span class="brand-image-xs logo-xl">
                @if(strlen($clinicLogo) > 0)
                    <img src="{{ env('OSS_URL').'/'.$clinicLogo }}" class="img-responsive" style="max-height: 40px;" alt="logo"/>
                @else
                    {{ env('WEBSITE_NAME') }}
                @endif
            </span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('admin') }}" class="nav-link{{ Route::current()->action['as'] === 'admin' ? ' active' : '' }}">
                            <i class="nav-icon fa fa-dashboard"></i>
                            <p>
                                @lang('general.dashboard')
                            </p>
                        </a>
                    </li>

                    {!! generateMenu() !!}
                    <li class="nav-item">
                        <a href="{{ route('admin.logout') }}" class="nav-link">
                            <i class="nav-icon fa fa-power-off"></i>
                            <p>
                                @lang('general.logout')
                            </p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        @yield('content')
    </div>

    <footer class="main-footer no-print">
        <div class="float-right d-none d-sm-inline">
            v1.0.0
        </div>
        <strong>Copyright &copy; {{ date('Y') }} {{ env('WEBSITE_NAME') }}.</strong> All rights reserved.
    </footer>

</div>
@section('script-bottom')
    <script src="{{ asset('/assets/cms/js/app.js') }}"></script>
    <script src="{{ asset('/assets/cms/js/moment.min.js') }}"></script>
    <script src="{{ asset('/assets/cms/js/money.js') }}"></script>
    <script src="{{ asset('/assets/cms/js/calendar/fullcalendar.min.js') }}"></script>
    <script src="{{ asset('/assets/cms/dropify/js/dropify.js')}}"> </script>
    <script src="{{ asset('/assets/cms/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js') }}"></script>
    @if(session()->has('message'))
        <?php
        switch (session()->get('message_alert')) {
            case 2 : $type = 'success'; break;
            case 3 : $type = 'info'; break;
            default : $type = 'danger'; break;
        }
        ?>
        <script type="text/javascript">
            'use strict';
            $.notify({
                // options
                message: '{!! session()->get('message') !!}'
            },{
                // settings
                type: '{!! $type !!}',
                placement: {
                    from: "bottom",
                    align: "right"
                },
            });
        </script>
    @endif
@show
</body>
</html>
