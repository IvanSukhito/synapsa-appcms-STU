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
                                            @if(strlen($data->image) != 0)
                                                <img class="profile-user-img img-fluid img-circle" src="{{ env('OSS_URL').'/'.$data->image }}" alt="User profile picture">
                                            @else
                                                <img class="profile-user-img img-fluid img-circle" src="{{ asset('/synapsaapps/users/user-default.png') }}" alt="User profile picture">
                                            @endif
                                        </div>

                                        <h3 class="profile-username text-center">{{ $data->fullname }}</h3>

                                        <p class="text-muted text-center">{{ $data->dob }}</p>

                                        <ul class="list-group list-group-unbordered mb-3">
                                            <li class="list-group-item">
                                                <b>Total Transaction</b> <a class="float-right">{{ $transactionLab->count() + $transactionProduct->count() + $transactionDoctor->count() }}</a>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Total Appointment</b> <a class="float-right">{{ $appointmentLab->count() + $appointmentDoctor->count() }}</a>
                                            </li>
                                        </ul>

                                            <div class="text-center badge-primary"><b>{{ $getListStatus[$data->status] }}</b></div>

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
                                                <!-- Transaction -->
                                                <ul class="nav nav-pills">
                                                    <li class="nav-item"><a class="nav-link" href="#lab" data-toggle="tab">@lang('general.transaction_lab')</a></li>
                                                    <li class="nav-item"><a class="nav-link active" href="#product" data-toggle="tab">@lang('general.transaction_product')</a></li>
                                                    <li class="nav-item"><a class="nav-link" href="#doctor" data-toggle="tab">@lang('general.transaction_doctor')</a></li>
                                                </ul>

                                                <div class="tab-content">
                                                    <div class="tab-pane" id="lab">

                                                        <br>
                                                        <table class="table table-bordered table-striped" id="dataT1">

                                                            <thead>
                                                            <tr>
                                                                <th>@lang('general.id')</th>
                                                                <th>@lang('general.transaction_code')</th>
                                                                <th>@lang('general.payment_name')</th>
                                                                <th>@lang('general.lab_name')</th>
                                                                <th>@lang('general.service')</th>
                                                                <th>@lang('general.total')</th>
                                                                <th>@lang('general.status')</th>
                                                                <th>@lang('general.tanggal_transaction')</th>
                                                                <th>@lang('general.action')</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($transactionLab as $list)
                                                                <tr>
                                                                    <td>{{ $list->id }}</td>
                                                                    <td>{{ $list->code }}</td>
                                                                    <td>{{ $list->payment_name }}</td>
                                                                    <td>{{ $list->lab_name }}</td>
                                                                    <td>{{ $list->category_service_name }}</td>
                                                                    <td>{{ number_format($list->total, 2) }}</td>
                                                                    <td>{{ $getListStatusTransaction[$list->status] }}</td>
                                                                    <td>{{ $list->created_at }}</td>
                                                                    <td>
                                                                        <a href="{{ route('admin.' . 'transaction-lab' . '.show', $list->id) }}" class="mb-1 btn btn-info btn-sm"
                                                                           title="@lang('general.show')">
                                                                            <i class="fa fa-eye"></i>
                                                                            <span class="d-none d-md-inline"> @lang('general.show')</span>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="tab-pane active" id="product">

                                                        <br>
                                                        <table class="table table-bordered table-striped" id="dataT2">

                                                            <thead>
                                                            <tr>
                                                                <th>@lang('general.id')</th>
                                                                <th>@lang('general.transaction_code')</th>
                                                                <th>@lang('general.payment_name')</th>
                                                                <th>@lang('general.product_name')</th>
                                                                <th>@lang('general.status')</th>
                                                                <th>@lang('general.tanggal_transaction')</th>
                                                                <th>@lang('general.total')</th>
                                                                <th>@lang('general.action')</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($transactionProduct as $list)
                                                                <tr>

                                                                    <td>{{ $list->id }}</td>
                                                                    <td>{{ $list->code }}</td>
                                                                    <td>{{ $list->payment_name }}</td>
                                                                    <td>{{ $list->product_name }}</td>
                                                                    <td>{{ $getListStatusTransaction[$list->status] }}</td>
                                                                    <td>{{ $list->created_at }}</td>
                                                                    <td>{{ number_format($list->total, 2) }}</td>
                                                                    <td>
                                                                        <a href="{{ route('admin.' . 'transaction-product' . '.show', $list->id) }}" class="mb-1 btn btn-info btn-sm"
                                                                             title="@lang('general.show')">
                                                                            <i class="fa fa-eye"></i>
                                                                            <span class="d-none d-md-inline"> @lang('general.show')</span>
                                                                        </a>
                                                                    </td>

                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="tab-pane" id="doctor">

                                                        <br>
                                                        <table class="table table-bordered table-striped" id="dataT3">

                                                            <thead>
                                                            <tr>
                                                                <th>@lang('general.id')</th>
                                                                <th>@lang('general.transaction_code')</th>
                                                                <th>@lang('general.service')</th>
                                                                <th>@lang('general.payment_name')</th>
                                                                <th>@lang('general.doctor_name')</th>
                                                                <th>@lang('general.total')</th>
                                                                <th>@lang('general.status')</th>
                                                                <th>@lang('general.tanggal_transaction')</th>
                                                                <th>@lang('general.action')</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($transactionDoctor as $list)
                                                                <tr>
                                                                    <td>{{ $list->id }}</td>
                                                                    <td>{{ $list->code }}</td>
                                                                    <td>{{ $list->payment_name }}</td>
                                                                    <td>{{ $list->category_service_name }}</td>
                                                                    <td>{{ $list->doctor_name }}</td>
                                                                    <td>{{ number_format($list->total, 2) }}</td>
                                                                    <td>{{ $getListStatusTransaction[$list->status] }}</td>
                                                                    <td>{{ $list->created_at }}</td>
                                                                    <td>
                                                                        <a href="{{ route('admin.' . 'transaction-doctor' . '.show', $list->id) }}" class="mb-1 btn btn-info btn-sm"
                                                                           title="@lang('general.show')">
                                                                            <i class="fa fa-eye"></i>
                                                                            <span class="d-none d-md-inline"> @lang('general.show')</span>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                            </div>
                                            <!-- /.tab-pane -->

                                            <div class="tab-pane" id="appointment">
                                                <!-- /.appointment -->
                                                <ul class="nav nav-pills">
                                                    <li class="nav-item"><a class="nav-link" href="#appointmentDoctor" data-toggle="tab">@lang('general.appointment_doctor')</a></li>
                                                    <li class="nav-item"><a class="nav-link active" href="#appointmentLab" data-toggle="tab">@lang('general.appointment_lab')</a></li>
                                                </ul>

                                                <div class="tab-content">
                                                    <div class="tab-pane" id="appointmentDoctor">

                                                        <br>
                                                        <table class="table table-bordered table-striped" id="dataA1">

                                                            <thead>
                                                            <tr>
                                                                <th>@lang('general.id')</th>
                                                                <th>@lang('general.appointment_code')</th>
                                                                <th>@lang('general.type_appointment')</th>
                                                                <th>@lang('general.doctor_name')</th>
                                                                <th>@lang('general.date')</th>
                                                                <th>@lang('general.status')</th>
                                                                <th>@lang('general.created_at')</th>
                                                                <th>@lang('general.action')</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($appointmentDoctor as $list)
                                                                <tr>
                                                                    <td>{{ $list->id }}</td>
                                                                    <td>{{ $list->code }}</td>
                                                                    <td>{{ $list->type_appointment }}</td>
                                                                    <td>{{ $list->doctor_name }}</td>
                                                                    <td>{{ $list->date }}</td>
                                                                    <td>{{ $getListStatusAppointment[$list->status] }}</td>
                                                                    <td>{{ $list->created_at }}</td>
                                                                    <td>
                                                                        @if($list->type_appointment == 'Homecare')
                                                                            <a href="{{ route('admin.' . 'doctor-clinic-homecare' . '.show', $list->id) }}" class="mb-1 btn btn-info btn-sm"
                                                                               title="@lang('general.show')">
                                                                                <i class="fa fa-eye"></i>
                                                                                <span class="d-none d-md-inline"> @lang('general.show')</span>
                                                                            </a>
                                                                        @elseif($list->type_appointment == 'Telemed')
                                                                            <a href="{{ route('admin.' . 'doctor-clinic-telemed' . '.show', $list->id) }}" class="mb-1 btn btn-info btn-sm"
                                                                               title="@lang('general.show')">
                                                                                <i class="fa fa-eye"></i>
                                                                                <span class="d-none d-md-inline"> @lang('general.show')</span>
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ route('admin.' . 'doctor-clinic-visit' . '.show', $list->id) }}" class="mb-1 btn btn-info btn-sm"
                                                                               title="@lang('general.show')">
                                                                                <i class="fa fa-eye"></i>
                                                                                <span class="d-none d-md-inline"> @lang('general.show')</span>
                                                                            </a>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="tab-pane active" id="appointmentLab">

                                                        <br>
                                                        <table class="table table-bordered table-striped" id="dataA2">

                                                            <thead>
                                                            <tr>
                                                                <th>@lang('general.id')</th>
                                                                <th>@lang('general.appointment_code')</th>
                                                                <th>@lang('general.type_appointment')</th>
                                                                <th>@lang('general.lab_name')</th>
                                                                <th>@lang('general.lab_price')</th>
                                                                <th>@lang('general.date')</th>
                                                                <th>@lang('general.time_start')</th>
                                                                <th>@lang('general.time_end')</th>
                                                                <th>@lang('general.status')</th>
                                                                <th>@lang('general.created_at')</th>
                                                                <th>@lang('general.action')</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($appointmentLab as $list)
                                                                <tr>
                                                                    <td>{{ $list->id }}</td>
                                                                    <td>{{ $list->code }}</td>
                                                                    <td>{{ $list->type_appointment }}</td>
                                                                    <td>{{ $list->lab_name }}</td>
                                                                    <td>{{ $list->lab_price }}</td>
                                                                    <td>{{ $list->date }}</td>
                                                                    <td>{{ $list->time_start }}</td>
                                                                    <td>{{ $list->time_end }}</td>
                                                                    <td>{{ $getListStatusAppointment[$list->status] }}</td>
                                                                    <td>{{ $list->created_at }}</td>
                                                                    <td>
                                                                        @if($list->type_appointment == 'Homecare')
                                                                        <a href="{{ route('admin.' . 'appointment-lab-homecare' . '.show', $list->id) }}" class="mb-1 btn btn-info btn-sm"
                                                                           title="@lang('general.show')">
                                                                            <i class="fa fa-eye"></i>
                                                                            <span class="d-none d-md-inline"> @lang('general.show')</span>
                                                                        </a>
                                                                        @else
                                                                            <a href="{{ route('admin.' . 'appointment-lab-visit' . '.show', $list->id) }}" class="mb-1 btn btn-info btn-sm"
                                                                               title="@lang('general.show')">
                                                                                <i class="fa fa-eye"></i>
                                                                                <span class="d-none d-md-inline"> @lang('general.show')</span>
                                                                            </a>
                                                                         @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

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
        table = jQuery('#dataT1, #dataT2, #dataT3').DataTable({
            pageLength: 5,
            autoWidth: false,
            scrollX: true,
            aaSorting: [ {!! "[0,'desc']" !!}],
            rowReorder: {
                selector: 'td:nth-child(2)'
            },
            responsive: true
        });
        table = jQuery('#dataA1, #dataA2').DataTable({
            pageLength: 5,
            autoWidth: false,
            scrollX: true,
            responsive: true,
            aaSorting: [ {!! "[0,'desc']" !!}],
        });
    </script>
@stop
