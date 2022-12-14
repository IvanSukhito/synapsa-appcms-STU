<?php
$daterange = app()->request->get('daterange');

if ($daterange) {
    $params['daterange'] = $daterange;
}
?>
@extends(env('ADMIN_TEMPLATE').'._base.layout')

@section('title', __('general.home'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>@lang('general.home')</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li><a href="<?php echo route('admin') ?>"><i class="fa fa-dashboard"></i> {{ __('general.home') }}</a></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Info Clinic</h3>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12">
                                <table>
                                    <tr>
                                        <td style="width:15%">Nama</td>
                                        <td style="width:1%">:</td>
                                        <th>{{ $clinic->name ?? '' }}</th>
                                    </tr>
                                    <tr>
                                        <td style="width:15%">Address</td>
                                        <td style="width:1%">:</td>
                                        <th>{{ $clinic->address ?? '' }}</th>
                                    </tr>
                                    <tr>
                                        <td style="width:15%">Nomor Telepon</td>
                                        <td style="width:1%">:</td>
                                        <th>{{ $clinic->no_telp ?? '' }}</th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <form method="get">
                    <div class="card">
                        <div class="card-header border-transparent">
                            <div class="col-md-12">
                                <label for="daterange">{{ __('general.daterange') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend datepicker-trigger">
                                        <div class="input-group-text">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                    </div>
                                    {{ Form::text('daterange', old('daterange', $daterange), ['id' => 'daterange', 'class' => 'form-control daterange', 'autocomplete' => 'off']) }}
                                </div>
                            </div>

                            <div class="mt-2 col-md-4">
                                <button class="btn btn-primary btn-sm" type="submit"
                                        title="@lang('general.filter')">
                                    <i></i>
                                    @lang('general.filter')
                                </button>
                                <a href="<?php echo route('admin') ?>" class="mb-1 btn btn-warning btn-sm" type="submit"
                                   title="@lang('general.reset')">
                                    <i></i>
                                    @lang('general.reset')
                                </a>

                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="row">

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary elevation-1"><i class="fa fa-tags"></i></span>

                                <div class="info-box-content">
                                    <a href="{{ route('admin.transaction-product.index') }}" style="color:black;">
                                    <span class="info-box-text">Transaksi Product</span>
                                    <span class="info-box-number">
                                    {!! $transactionProduct->count() ?? 0 !!}
                                    </span>
                                    </a>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-user-md"></i></span>

                                <div class="info-box-content">
                                    <a href="{{ route('admin.transaction-doctor.index') }}" style="color:black;">
                                    <span class="info-box-text">Transaksi Dokter</span>
                                    <span class="info-box-number">
                                    {!! $transactionDoctor->count() ?? 0 !!}
                                    </span>
                                    </a>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success elevation-1"><i class="fa fa-flask"></i></span>

                                <div class="info-box-content">
                                    <a href="{{ route('admin.transaction-lab.index') }}" style="color:black;">
                                    <span class="info-box-text">Transaksi Lab</span>
                                    <span class="info-box-number">
                                    {!! $transactionLab->count() ?? 0 !!}
                                    </span>
                                    </a>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box">

                               <span class="info-box-icon bg-warning elevation-1"><i class="fa fa-users"></i></span>

                                <div class="info-box-content">
                                    <a href="{{ route('admin.user-clinic.index') }}" style="color:black;">
                                    <span class="info-box-text">Pasien Terdaftar</span>
                                    <span class="info-box-number">
                                    {!! $user->count() ?? 0 !!}
                                    </span>
                                    </a>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

@stop

@section('script-bottom')
    @parent
    @include(env('ADMIN_TEMPLATE').'._component.generate_forms_script')
    <script src="{{ asset('assets/cms/js/highcharts/highcharts.js') }}"></script>

    <script type="text/javascript">
        $('#daterange').daterangepicker({
            // timePicker: true,
            // timePicker24Hour: true,
            // timePickerIncrement: 15,
            autoUpdateInput: false,
            // startDate: moment().startOf('days').subtract('30', 'days'),
            locale: {
                "format": "YYYY-MM-DD",
                "separator": " | "
            }
        });

        $(document).ready(function() {

            $('#patient').click(function (){
                let patient = $(this).val();
                console.log(patient);
            });

        });
    </script>
@stop
