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
        <link rel="stylesheet" href="{{ asset('assets/cms/css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cms/js/dropify/dist/css/dropify.min.css') }}">
    @show
    @section('script-top')
    @show
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>
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
            <span class="brand-image-xl logo-xs">{{ substr(env('WEBSITE_NAME'), 0, 2) }}</span>
            <span class="brand-image-xs logo-xl">{{ env('WEBSITE_NAME') }}</span>
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
