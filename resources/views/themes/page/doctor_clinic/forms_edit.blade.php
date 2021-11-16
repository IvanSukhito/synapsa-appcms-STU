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

            <div class="card {!! $printCard !!}">
                <div class="card-header">
                    <h3 class="card-title">@lang('doctor')</h3>
                </div>
                <!-- /.card-header -->

                <div class="card-body">
                    @include(env('ADMIN_TEMPLATE').'.page.doctor_clinic.generate_forms_2')
                    @if(in_array($viewType, ['create','edit']))
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="province_id">{{ __('general.province') }} <span class="text-red">*</span></label>
                                    <select name="province_id" id="province_id" class="form-control input-lg select2" required>
                                        <option value="#" readonly="readonly">Province</option>
                                        @foreach($province as $listProvince)
                                            <option value="{{$listProvince->id}}">{{$listProvince->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="city_id">{{ __('general.city') }} <span class="text-red">*</span></label>
                                    <select name="city_id" id="city_id" class="form-control select2 city"required>
                                        <option value="">City</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="district_id">{{ __('general.district') }} <span class="text-red">*</span></label>
                                    <select name="district_id" id="district_id" class="form-control select2 district"required>
                                        <option value="">District</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sub_district_id">{{ __('general.sub_district') }} <span class="text-red">*</span></label>
                                    <select name="sub_district_id" id="sub_district_id" class="form-control select2 sub_district"required>
                                        <option value="">Sub District</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::text('zip_code', old('zip_code', isset($dataUser->zip_code) ? $dataUser->zip_code : null), ['class' => $errors->has('zip_code') ? 'form-control' : 'form-control', 'id' => 'zip_code', 'required' => true, 'placeholder' => 'Masukan kode pos ', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::text('address', old('address', isset($dataUser->address) ? $dataUser->address : null), ['class' => $errors->has('address') ? 'form-control' : 'form-control', 'id' => 'address', 'required' => true, 'placeholder' => 'e.g Rumah, Kantor', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address_detail">{{ __('general.address_detail') }} <span class="text-red">*</span></label>
                                    {{ Form::textarea('address_detail', old('address_detail', isset($dataUser->address_detail) ? $dataUser->address_detail : null), ['class' => $errors->has('address_detail') ? 'ckeditor' : 'ckeditor', 'id' => 'address_detail', 'required' => true, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>


                    @endif
                </div>
                <!-- /.card-body -->

            </div>

            <div class="card {!! $printCard !!}">
                <div class="card-header">
                    <h3 class="card-title">@lang('service')</h3>
                </div>
                <!-- /.card-header -->

                <div class="card-body">
                    @include(env('ADMIN_TEMPLATE').'._component.generate_forms')
                    @if(in_array($viewType,['create']))
                        <div class="form-group">
                            <p id="infoService">Input 0 Service</p>
                        </div>
                    @endif
                    <div id="listDoctorService"></div>

                </div>
                <!-- /.card-body -->

            </div>

            @if(in_array($viewType, ['show']))
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="data1">
                        <thead>
                        <tr>
                            <th>@lang('general.id')</th>
                            <th>@lang('general.doctor_id')</th>
                            <th>@lang('general.service_id')</th>
                            <th>@lang('general.date_available')</th>
                            <th>@lang('general.time_start')</th>
                            <th>@lang('general.time_end')</th>
                            <th>@lang('general.book')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($getScheduleData as $list)
                            <tr>
                                <td>{{ $list->id }}</td>
                                <td>{{ $list->doctor_id }}</td>
                                <td>{{ $list->service_id }}</td>
                                <td>{{ $list->date_available }}</td>
                                <td>{{ $list->time_start }}</td>
                                <td>{{ $list->time_end }}</td>
                                <td>{{ $getListAvailabe[$list->book] ?? $list->book }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
        @endif
        <!-- /.card-body -->


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
        </div>
        {{ Form::close() }}

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

            $(document).ready(function() {
                // $('#service_id').change();
                //  $('#listDoctorService').change();
                $('.multiselect2').select2({
                    tags: true
                });

                let html = '';
                let i = 0;
                let getServiceId = [];
                $.each(listDataService, function(index, item) {

                    $.each(listDoctorService, function(index2, item2){
                        if (parseInt(index) === parseInt(item2.service_id)) {
                            var getService = item;
                            var getPrice = item2.price;

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

                        getServiceId.push(ServiceId)

                        $('#service_id').val(getServiceId);

                        let totalService = listDoctorService.length;
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

        });
    </script>
@stop
