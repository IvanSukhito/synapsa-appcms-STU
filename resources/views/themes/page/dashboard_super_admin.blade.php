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
                    <h1>@lang('general.dashboard')</h1>
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
                <div class="col-12 col-sm-6 col-md-12">
                    <div class="info-box">
                        <div class="col-md-12">
                            <form method="get">

                                <div class="input-group">
                                    <input type="text" class="form-control daterange"  name="daterange" placeholder="Search By Date Range">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-medium btn-primary">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="input-group-append">
                                        <a href="<?php echo route('admin') ?>"class="btn btn-medium btn-outline-info">
                                            <i class="fa fa-recycle"></i>
                                        </a>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="row">

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary elevation-1"><i class="fa fa-hospital-o"></i></span>

                                <div class="info-box-content">
                                    <a href="{{ route('admin.klinik.index') }}" style="color:black;">
                                        <span class="info-box-text">Clinic Active</span>
                                        <span class="info-box-number">
                                    {!! $clinic->count() ?? 0 !!}
                                    </span>
                                    </a>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-users"></i></span>

                                <div class="info-box-content">
                                    <a href="{{ route('admin.users-patient.index') }}" style="color:black;">
                                        <span class="info-box-text">Users Registered</span>
                                        <span class="info-box-number">
                                    {!! $userPatient->count() ?? 0 !!}
                                    </span>
                                    </a>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                            <!-- /.info-box -->
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success elevation-1"><i class="fa fa-user-md"></i></span>

                                <div class="info-box-content">
                                    <a href="{{ route('admin.users-doctor.index') }}" style="color:black;">
                                        <span class="info-box-text">Doctor Resgitered</span>
                                        <span class="info-box-number">
                                    {!! $userDoctor->count() ?? 0 !!}
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

            <div class="row">
                <div class="col-md-8">
                    <div class="card card-dark">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Transaction Products</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-tool" data-card-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-tool" data-card-widget="remove">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($statusTransaction as $key => $listTransaction)
                                    <tr>
                                        <td>
                                            @if($listTransaction == 'Pending')
                                                <a href="{{ route('admin.transaction-product-admin.index') }}?status={{ $key }}"><span class="badge badge-warning">{{ $listTransaction }}</span></a></td>
                                            @elseif($listTransaction == 'Payment Pending')
                                                <a href="{{ route('admin.transaction-product-admin.index') }}?status={{ $key }}"><span class="badge badge-info">{{ $listTransaction }}</span></a></td>
                                            @elseif($listTransaction == 'Payment Received')
                                                <a href="{{ route('admin.transaction-product-admin.index') }}?status={{ $key }}"><span class="badge badge-primary">{{ $listTransaction }}</span></a></td>
                                            @elseif($listTransaction == 'Complete')
                                            <a href="{{ route('admin.transaction-product-admin.index') }}?status={{ $key }}"><span class="badge badge-success">{{ $listTransaction }}</span></a></td>
                                            @elseif($listTransaction == 'Proses')
                                            <a href="{{ route('admin.transaction-product-admin.index') }}?status={{ $key }}"><span class="badge badge-light">{{ $listTransaction }}</span></a></td>
                                            @elseif($listTransaction == 'Proses Pengiriman')
                                            <a href="{{ route('admin.transaction-product-admin.index') }}?status={{ $key }}"><span class="badge badge-dark">{{ $listTransaction }}</span></a></td>
                                            @elseif($listTransaction == 'Void')
                                            <a href="{{ route('admin.transaction-product-admin.index') }}?status={{ $key }}"><span class="badge badge-dark">{{ $listTransaction }}</span></a></td>
                                            @else
                                            <a href="{{ route('admin.transaction-product-admin.index') }}?status={{ $key }}"><span class="badge badge-danger">{{ $listTransaction }}</span></a></td>
                                        @endif
                                        <td>
                                            <a href="#"><b> {{$transactionProduct->where('status', $key)->count()}}</b> </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer clearfix">
                            <a href="{{ route('admin.transaction-product-admin.index') }}" class="btn btn-sm btn-outline-primary float-right">View All</a>
                        </div>
                        <!-- /.card-footer -->
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Info Boxes Style 2 -->
                    <div class="info-box mb-3 bg-primary">
                        <span class="info-box-icon"><i class="fa fa-tags"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Transaction Product</span>
                            <span class="info-box-number">{{ $transactionProduct->count() }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                    <div class="info-box mb-3 bg-danger">
                        <span class="info-box-icon"><i class="fa fa-user-md"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Transaction Doctor</span>
                            <span class="info-box-number">{{ $transactionDoctor->count() }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                    <div class="info-box mb-3 bg-success">
                        <span class="info-box-icon"><i class="fa fa-flask"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Transaction Lab</span>
                            <span class="info-box-number">{{ $transactionLab->count() }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                    <div class="info-box mb-3 bg-info">
                        <span class="info-box-icon"><i class="fa fa-medkit"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Appointment Doctor</span>
                            <span class="info-box-number">{{ $appointmentDoctor->count() }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                    <div class="info-box mb-3 bg-light">
                        <span class="info-box-icon"><i class="fa fa-calendar-check-o"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Appointment Lab</span>
                            <span class="info-box-number">{{ $appointmentLab->count() }}</span>
                        </div>
                        <!-- /.info-box-content -->
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
