<?php
switch ($viewType) {
    case 'create': $printCard = 'card-success'; break;
    case 'edit': $printCard = 'card-primary'; break;
    default: $printCard = 'card-info'; break;
}
if (in_array($viewType, ['show'])) {
    $addAttribute = [
        'disabled' => true
    ];
}
else {
    $addAttribute = [
    ];
}
?>
@extends(env('ADMIN_TEMPLATE').'._base.layout')

@section('title', $formsTitle)

@section('css')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('script-top')
    @parent
    <script>
        CKEDITOR_BASEPATH = '/assets/cms/js/ckeditor/';
    </script>
@stop

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $thisLabel }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo route('admin') ?>"><i class="fa fa-dashboard"></i> {{ __('general.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo route('admin.' . $thisRoute . '.index') ?>"> {{ __('general.title_home', ['field' => $thisLabel]) }}</a></li>
                        <li class="breadcrumb-item active">{{ $formsTitle }}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            @if(in_array($viewType, ['create']))
                {{ Form::open(['route' => ['admin.' . $thisRoute . '.store'], 'files' => true, 'id'=>'form', 'role' => 'form'])  }}
            @elseif(in_array($viewType, ['edit']))
                {{ Form::open(['route' => ['admin.' . $thisRoute . '.update', $data->{$masterId}], 'method' => 'PUT', 'files' => true, 'id'=>'form', 'role' => 'form'])  }}
            @else
                {{ Form::open(['id'=>'form', 'role' => 'form'])  }}
            @endif


                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-3">

                                <!-- Profile Image -->
                                <div class="card card-primary card-outline">
                                    <div class="card-body box-profile">
                                        <div class="text-center">
                                            <img class="profile-user-img img-fluid img-circle" src="../../dist/img/user4-128x128.jpg" alt="User profile picture">
                                        </div>

                                        <h3 class="profile-username text-center">{{ $data->fullname }}</h3>

                                        <p class="text-muted text-center">{{ $data->dob }}</p>

                                        <ul class="list-group list-group-unbordered mb-3">
                                            <li class="list-group-item">
                                                <b>Total Transaction</b> <a class="float-right">{{ $transaction->count() }}</a>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Total Appointment</b> <a class="float-right">0</a>
                                            </li>
                                        </ul>

                                            <a href="#" class="btn btn-outline-primary btn-block"><b>{{ $getListStatus[$data->status] }}</b></a>

                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <!-- /.card -->

                                <!-- About Me Box -->
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">About Me</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <strong><i class="fa fa-address-book-o"></i> NIK</strong>

                                        <p class="text-muted">
                                           {{ $data->nik }}
                                        </p>

                                        <hr>

                                        <strong><i class="fa fa-map-marker"></i> Location</strong>

                                        <p class="text-muted">{{ strip_tags($data->address_detail) }}, {{ $city->name }} , {{$province->name}}</p>

                                        <hr>

                                        <strong><i class="fa fa-mail-forward"></i> Email </strong>

                                        <p class="text-muted">{{ strip_tags($data->email) }}</p>

                                        <hr>

                                        <strong><i class="fa fa-mobile-phone"></i> Phone </strong>

                                        <br>
                                        <p class="text-muted"> {{ $data->phone }} </p>
                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <!-- /.card -->
                            </div>
                            <!-- /.col -->
                            <div class="col-md-9">
                                <div class="card">
                                    <div class="card-header p-2">
                                        <ul class="nav nav-pills">
                                            <li class="nav-item"><a class="nav-link" href="#biodata" data-toggle="tab">@lang('Biodata')</a></li>
                                            <li class="nav-item"><a class="nav-link active" href="#transaction" data-toggle="tab">@lang('Transaction')</a></li>
                                            <li class="nav-item"><a class="nav-link" href="#appointment" data-toggle="tab">@lang('Appointment')</a></li>
                                        </ul>
                                    </div><!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="tab-content">
                                            <div class="tab-pane" id="biodata">
                                                @include(env('ADMIN_TEMPLATE').'._component.generate_forms')
                                            </div>
                                            <!-- /.tab-pane -->
                                            <div class="tab-pane active" id="transaction">
                                                <!-- The timeline -->
                                                <table class="table table-bordered table-striped" id="data1">

                                                    <thead>
                                                    <tr>
                                                        <th>@lang('general.id')</th>
                                                        <th>@lang('general.transaction_code')</th>
                                                        <th>@lang('general.payment_name')</th>
                                                        <th>@lang('general.total')</th>
                                                        <th>@lang('general.type_transaction')</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($transaction as $list)
                                                        <tr>
                                                            <td>{{ $list->id }}</td>
                                                            <td>{{ $list->code }}</td>
                                                            <td>{{ $list->payment_name }}</td>
                                                            <td>{{ number_format($list->total, 2) }}</td>
                                                            <td>{{ $list->type_service_name }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <!-- /.tab-pane -->

                                            <div class="tab-pane" id="appointment">
                                                <!-- /.appointment -->
                                            </div>
                                            <!-- /.tab-pane -->
                                        </div>
                                        <!-- /.tab-content -->
                                    </div><!-- /.card-body -->
                                </div>
                                <!-- /.card -->
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div><!-- /.container-fluid -->
                </section>

            <div class="card-footer">

                @if(in_array($viewType, ['create']))
                    <button type="submit" class="mb-2 mr-2 btn btn-success" title="@lang('general.save')">
                        <i class="fa fa-save"></i><span class=""> @lang('general.save')</span>
                    </button>
                @elseif (in_array($viewType, ['edit']))
                    <button type="submit" class="mb-2 mr-2 btn btn-primary" title="@lang('general.update')">
                        <i class="fa fa-save"></i><span class=""> @lang('general.update')</span>
                    </button>
                @elseif (in_array($viewType, ['show']) && $permission['edit'] == true)
                    <a href="<?php echo route('admin.' . $thisRoute . '.edit', $data->{$masterId}) ?>"
                       class="mb-2 mr-2 btn btn-primary" title="{{ __('general.edit') }}">
                        <i class="fa fa-pencil"></i><span class=""> {{ __('general.edit') }}</span>
                    </a>
                @endif
                <a href="<?php echo route('admin.' . $thisRoute . '.index') ?>" class="mb-2 mr-2 btn btn-warning"
                   title="{{ __('general.back') }}">
                    <i class="fa fa-arrow-circle-o-left"></i><span class=""> {{ __('general.back') }}</span>
                </a>

            </div>

        {{ Form::close() }}

        </div>
    </section>

@stop

@section('script-bottom')
    @parent
    @include(env('ADMIN_TEMPLATE').'._component.generate_forms_script')
    <script type="text/javascript">
        'use strict';
        let table;
        table = jQuery('#data1').DataTable({
            autoWidth: false,
            scrollX: true,
            rowReorder: {
                selector: 'td:nth-child(2)'
            },
            responsive: true
        });
    </script>
@stop
