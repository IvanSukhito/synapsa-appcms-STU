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
                    @if(in_array($viewType, ['create']))
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="province_id">{{ __('general.province') }} <span class="text-red">*</span></label>
                                    <select name="province_id" id="province_id" class="form-control input-lg select2" required>
                                        <option value="#" readonly="readonly">Select</option>
                                        @foreach($province as $listProvince)
                                            <option value="{{$listProvince->id}}">{{$listProvince->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city_id">{{ __('general.city') }} <span class="text-red">*</span></label>
                                    <select name="city_id" id="city_id" class="form-control select2 city"required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="district_id">{{ __('general.district') }} <span class="text-red">*</span></label>
                                    <select name="district_id" id="district_id" class="form-control select2 district"required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sub_district_id">{{ __('general.sub_district') }} <span class="text-red">*</span></label>
                                    <select name="sub_district_id" id="sub_district_id" class="form-control select2 sub_district"required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
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
        $(document).ready(function() {

            $('#province_id').change(function() {

                let ProvinceId = $('#province_id').val();
                var div= $('#city_id').parent();
                var op="";

                $.ajax({
                    type: 'GET',
                    url: "{{ route('admin.findCity') }}",
                    data: { province_id :ProvinceId},
                    success : function (data){

                        op+='<option value="0" selected disabled>chose city</option>';
                        for(var i=0;i<data.length;i++){
                            op+='<option value="'+data[i].id+'">'+data[i].name+'</option>';
                        }

                        div.find('#city_id').html(" ");
                        div.find('#city_id').append(op);

                    },
                    error: function (){

                    }
                })
            });

            $('#city_id').change(function() {

                let CityId = $('#city_id').val();
                var div= $('#district_id').parent();
                var op="";

                $.ajax({
                    type: 'GET',
                    url: "{{ route('admin.findDistrict') }}",
                    data: { city_id :CityId},
                    success : function (data){

                        op+='<option value="0" selected disabled>chose district</option>';
                        for(var i=0;i<data.length;i++){
                            op+='<option value="'+data[i].id+'">'+data[i].name+'</option>';
                        }

                        div.find('#district_id').html(" ");
                        div.find('#district_id').append(op);

                    },
                    error: function (){

                    }
                })
            });

            $('#district_id').change(function() {

                let DistrictId = $('#district_id').val();
                var div= $('#sub_district_id').parent();
                var op="";

                $.ajax({
                    type: 'GET',
                    url: "{{ route('admin.findSubDistrict') }}",
                    data: { district_id :DistrictId},
                    success : function (data){

                        op+='<option value="0" selected disabled>chose sub district</option>';
                        for(var i=0;i<data.length;i++){
                            op+='<option value="'+data[i].id+'">'+data[i].name+'</option>';
                        }

                        div.find('#sub_district_id').html(" ");
                        div.find('#sub_district_id').append(op);

                    },
                    error: function (){

                    }
                })
            });


        });
    </script>
@stop
