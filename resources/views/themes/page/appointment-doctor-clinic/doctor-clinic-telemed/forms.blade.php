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
            <div class="card {!! $printCard !!}">
                <div class="card-header">
                    <h3 class="card-title">{{ $formsTitle }}</h3>
                </div>
                <!-- /.card-header -->

                @if(in_array($viewType, ['create']))
                    {{ Form::open(['route' => ['admin.' . $thisRoute . '.store'], 'files' => true, 'id'=>'form', 'role' => 'form'])  }}
                @elseif(in_array($viewType, ['edit']))
                    {{ Form::open(['route' => ['admin.' . $thisRoute . '.update', $data->{$masterId}], 'method' => 'PUT', 'files' => true, 'id'=>'form', 'role' => 'form'])  }}
                @else
                    {{ Form::open(['id'=>'form', 'role' => 'form'])  }}
                @endif

                <div class="card-body">
                    @include(env('ADMIN_TEMPLATE').'._component.generate_forms')
                    @if(in_array($viewType, ['show']))

                        <label for="doctor_prescription">{{ __('general.doctor_prescription') }}</label>
                        <hr>
                        @if(isset($data->doctor_prescription) == 1)
                            @foreach(json_decode($data->doctor_prescription) as $list)
                                <a href="{{ $list }}" target="_blank" title="doctor-prescription"  data-fancybox>
                                    <img src="{{ $list }}" class="img-responsive max-image-preview" alt="doctor-prescription"/>
                                </a>
                            @endforeach
                        @else
                            <a href="{{ asset('assets/cms/images/no-img.png') }}" target="_blank" title="doctor-prescription"  data-fancybox>
                                <img src="{{ asset('assets/cms/images/no-img.png') }}" class="img-responsive max-image-preview" alt="doctor-prescription"/>
                            </a>
                        @endif
                        <hr>
                            <div class="card-body">
                                <table class="table table-bordered table-striped" id="data1">
                                    <thead>
                                    <tr>
                                        <th>@lang('general.id')</th>
                                        <th>@lang('general.product_name')</th>
                                        <th>@lang('general.jumlah_disarankan')</th>
                                        <th>@lang('general.jumlah_dicheckout')</th>
                                        <th>@lang('general.product_price')</th>
                                        <th>@lang('general.dose')</th>
                                        <th>@lang('general.type_dose')</th>
                                        <th>@lang('general.period')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($doctorProduct as $list)
                                        <tr>
                                            <td>{{ $list->id }}</td>
                                            <td>{{ $list->product_name }}</td>
                                            <td>{{ $list->product_qty }}</td>
                                            <td>{{ $list->product_qty_checkout }}</td>
                                            <td>{{ number_format($list->product_price, 2) }}</td>
                                            <td>{{ $list->dose }}</td>
                                            <td>{{ $listSetTypeDose[$list->type_dose] ?? 'Tidak Ada'}}</td>
                                            <td>{{ $list->period }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                        </div>
                    @endif
                </div>
                <!-- /.card-body -->

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
        </div>
    </section>

@stop

@section('script-bottom')
    @parent
    @include(env('ADMIN_TEMPLATE').'._component.generate_forms_script')
    <script>
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
