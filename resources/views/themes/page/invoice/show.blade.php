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

$no = 1;
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

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="invoice p-4 mb-3">
                            <!-- title row -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="logo p-5">
                                                <img class="img-fluid" alt="Synapsa Logo" src="https://unictive.oss-ap-southeast-5.aliyuncs.com/synapsaklinik/uploads/logo/JRyRXJATtXC8FF9rOmJXpGrMV4ZPjCQ5bNOGQyNf.png">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h4><strong>@lang('general.title_invoice')</strong></h4><br />
                                            <div class="row">
                                                <div class="col-md-5">@lang('general.date')</div>
                                                <div class="col-md-6 border text-center">{{ date('d F Y', strtotime($data->transaction_date)) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.col -->
                            </div>
                            <br />
                            <!-- info row -->
                            <div class="row invoice-info">
                                <div class="col-sm-6 invoice-col">
                                    <div class="text-white bg-blue col-sm-12 pl-1"><strong>@lang('general.vendor')</strong></div>
                                    <address class="pl-1">
                                        <strong>Synapsa</strong><br>
                                        Synapsa Address
                                    </address>
                                </div>
                                <!-- /.col -->
                                <div class="col-sm-6 invoice-col">
                                    <div class="text-white bg-blue col-sm-12 pl-1"><strong>@lang('general.ship_to')</strong></div>
                                    <address class="pl-1">
                                        <strong>{{ $data->klinik_name }}</strong><br />
                                        {{ $data->klinik_address }}<br />
                                        {{ '+62 ' . $data->klinik_no_telp }}<br />
                                        {{ $data->klinik_email }}<br />
                                    </address>
                                </div>
                            </div>
                            <!-- /.row -->
                            <br />
                            <!-- Table row -->
                            <div class="row">
                                <div class="col-12 table-responsive">
                                    <table class="table table-bordered text-center">
                                        <thead class="bg-blue">
                                        <tr>
                                            <th>No.</th>
                                            <th>@lang('general.name')</th>
                                            <th>@lang('general.price_klinik')</th>
                                            <th>@lang('general.price_synapsa')</th>
                                            <th>@lang('general.qty')</th>
                                            <th>@lang('general.total')</th>
                                        </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $data->product_name }}</td>
                                            <td>{{ number_format($data->price_product_klinik, 2)  }}</td>
                                            <td>{{ number_format($data->price_product_synapsa, 2) }}</td>
                                            <td>{{ $data->total_qty_transaction }}</td>
                                            <td>{{ number_format($data->total_price_transaction, 2) }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /.row -->

                            <br />

                            <!-- Table row -->
                            <div class="row">
                                <div class="col-6 table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="bg-blue">
                                        <tr class="text-center">
                                            <th>@lang('general.notes_and_instruction')</th>
                                        </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                        <tr>
                                            <td>Notes and Instruction</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-6 table-responsive">
                                    <table class="table table-bordered bg-white">
                                        <tr>
                                            <td class="p-1"><strong>@lang('general.total')</strong></td>
                                            <td class="p-1 text-right"><strong>{{ number_format($data->total_price_transaction, 2) }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 text-center">
                                </div>
                                <div class="col-6 text-center">
                                    <div class="logo">
                                        <br />
                                        <br />
                                        <br />
                                        <br />
                                        <br />
                                        <br />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 text-center">
                                    {{ date('d F Y', strtotime($data->transaction_date)) }}
                                    <div class="border text-center">@lang('general.date')</div>
                                </div>
                            </div>

                            <br />

                            <!-- this row will not appear when printing -->
                            <div class="row no-print">
                                <div class="col-12">
                                    <div class="row no-print">
                                        <div class="col-12">
                                            <a href="" onclick="event.preventDefault(); window.print()" class="mb-2 mr-2 btn btn-primary"><i class="fa fa-print"></i> Print</a>
                                            <a href="<?php echo route('admin.' . $thisRoute . '.index') ?>" class="mb-2 mr-2 btn btn-warning"
                                               title="{{ __('general.back') }}">
                                                <i class="fa fa-arrow-circle-o-left"></i><span class=""> {{ __('general.back') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>

@stop

@section('script-bottom')
    @parent
    @include(env('ADMIN_TEMPLATE').'._component.generate_forms_script')
@stop
