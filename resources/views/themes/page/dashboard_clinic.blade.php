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
                                        <th>{{ $clinic->name }}</th>
                                    </tr>
                                    <tr>
                                        <td style="width:15%">Address</td>
                                        <td style="width:1%">:</td>
                                        <th>{{ $clinic->address }}</th>
                                    </tr>
                                    <tr>
                                        <td style="width:15%">Nomor Telepon</td>
                                        <td style="width:1%">:</td>
                                        <th>{{ $clinic->no_telp }}</th>
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
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="row">

                        <div class="col-md-4">
                            <!-- small box -->

                            <a href="#" class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{!! $user->count() ?? 0 !!}</h3>

                                    <p>Pasien Terdaftar</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-4">
                            <!-- small box -->

                            <a href="#" class="small-box bg-primary">
                                <div class="inner">
                                    <h3>0</h3>

                                    <p>Ringkasan Transaksi</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-4">
                            <!-- small box -->

                            <a href="#" class="small-box bg-info">
                                <div class="inner">
                                    <h3>{!! $transactionLab->count() ?? 0 !!}</h3>

                                    <p>Lab Transaksi</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-4">
                            <!-- small box -->
                            <a href="#" class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{!! $transactionDoctor->count() ?? 0 !!}</h3>

                                    <p>Dokter Transaksi</p>
                                </div>
                            </a>
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
    </script>
@stop
