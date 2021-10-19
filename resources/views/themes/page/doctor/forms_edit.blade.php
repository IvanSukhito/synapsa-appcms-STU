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

                    @if(in_array($viewType,['edit']))
                        <div class="form-group">
                            <p id="infoService">Total 0 Service</p>
                        </div>
                        <div id="listDoctorService"></div>
                    @endif
                </div>

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
        table = jQuery('#data1').DataTable();

        let listDataService = JSON.parse('{!! json_encode($listSet['service_id']) !!}');
        let listDoctorService = JSON.parse('{!! $doctorService !!}');

        $(document).ready(function() {
           // $('#service_id').change();
           //  $('#listDoctorService').change();
            $('.multiselect2').select2({
                tags: true
            });

            let html = '';
            let i = 0;
            $.each(listDataService, function(index, item) {

                $.each(listDoctorService, function(index2, item2){

                    if (parseInt(index) === parseInt(item2.service_id)) {
                        var getService = item;
                        var getPrice = item2.price;

                        console.log(i);
                        html += '<div class="form-group">' +
                            '<label for="service">{{ __('general.service') }} ' + (i+1) + ' <span class="text-red">*</span></label>' +
                            '<input type="text" id="service_' + i +'" name="service[' + i + ']" class="form-control" placeholder="@lang('general.service')" disabled value="' + getService + '"> ' +
                            '</div>'+
                            '<div class="form-group">' +
                            '<label for="price">{{ __('general.price') }} ' + (i+1) + ' <span class="text-red">*</span></label>' +
                            '<input type="text" id="price_' + i +'" name="price[' + i + ']" class="form-control setMoney" placeholder="@lang('general.price')" value="' + getPrice + '"> ' +
                            '</div>';
                        i++;

                    }

                    var ServiceId = item2.service_id;
                    var getServiceId = [];
                    var a = 0;

                    for (a = 1; a <= ServiceId; a++){
                        getServiceId.push(a)
                        //array.push is used to push a value inside array
                    }

                    $('#service_id').val(getServiceId);

                    let totalService = getServiceId.length;
                    $('#infoService').html('Total' + '&nbsp;' + totalService +'&nbsp;'+'Service');


                    $('#listDoctorService').html(html);

                    $('.setMoney').inputmask('numeric', {
                        radixPoint: ".",
                        groupSeparator: ",",
                        digits: 2,
                        autoGroup: true,
                        prefix: '', //Space after $, this will not truncate the first character.
                        rightAlign: false
                    });
                });
            });
            $('#service_id').select2('destroy').attr('readonly', true).css({'-moz-appearance': 'none','-webkit-appearance': 'none'});
        });

        {{--$('#a').on('change',function(){--}}
        {{--    let html = '';--}}
        {{--    let i = 0;--}}
        {{--    $.each(listDoctorService, function(index, item){--}}
        {{--        var Service = item.service_id;--}}
        {{--        var Price = item.price;--}}
        {{--        var Doctor = item.doctor_id;--}}

        {{--        html += '<div class="form-group">' +--}}
        {{--            //'<select id='+--}}
        {{--            '<label for="service">{{ __('general.service') }} ' + (i + 1) + ' <span class="text-red">*</span></label>' +--}}
        {{--            '<input type="text" id="service_' + i +'" name="service[' + i + ']" class="form-control" disabled placeholder="@lang('general.service')"  value="' + listDataService[Service] + '"> ' +--}}
        {{--            '</div>'+--}}
        {{--            '<div class="form-group">' +--}}
        {{--            '<label for="price">{{ __('general.price') }} ' + (i + 1) + ' <span class="text-red">*</span></label>' +--}}
        {{--            '<input type="text" id="price_' + i +'" name="price[' + i + ']" class="form-control setMoney" placeholder="@lang('general.price')"  value="' + Price + ' "> ' +--}}
        {{--            '</div>';--}}


        {{--        $('#listDoctorService').html(html);--}}

        {{--        console.log(Service);--}}
        {{--        $('#service_id').val(Service);--}}

        {{--        $('.setMoney').inputmask('numeric', {--}}
        {{--            radixPoint: ".",--}}
        {{--            groupSeparator: ",",--}}
        {{--            digits: 2,--}}
        {{--            autoGroup: true,--}}
        {{--            prefix: '', //Space after $, this will not truncate the first character.--}}
        {{--            rightAlign: false--}}
        {{--        });--}}
        {{--    });--}}
        {{--});--}}


    </script>
@stop
