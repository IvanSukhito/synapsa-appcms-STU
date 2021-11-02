@extends('errors::minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('scriptTop')
    <script type="text/javascript">
        'use strict';
        window.location = "{!! route('admin.login') !!}";
    </script>
@endsection
@section('message')
    <a href="{!! route('admin.login') !!}">Page Expired, Click here to login page</a>
@endsection
