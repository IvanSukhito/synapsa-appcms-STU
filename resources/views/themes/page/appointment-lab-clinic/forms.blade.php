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
                    <h3 class="card-title">@lang('general.appointment_lab')</h3>
                </div>
                <!-- /.card-header -->

                <div class="card-body">
                    @include(env('ADMIN_TEMPLATE').'._component.generate_forms')
                    @if(in_array($viewType, ['show']))
                        @if(strlen($data->form_patient_full) > 0)
                            <div class="form-group">
                                <label>@lang('general.form_patient')</label>
                                <br />
                                <a class="btn btn-info btn-sm" href="{{ $data->form_patient_full }}">@lang('general.download')</a>
                            </div>
                        @endif

                        <h3 class="card-title"><strong>Layanan Lab</strong></h3>
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>@lang('general.nomor')</th>
                                <th>@lang('general.layanan_lab')</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $no = 1;
                            ?>
                            @foreach($dataDetails as $details)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $details->lab_name }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <!-- /.card-body -->

            </div>

                @if($dataUser)
                    <div class="card {!! $printCard !!}">
                        <div class="card-header">
                            <h3 class="card-title">@lang('general.biodata_user')</h3>
                        </div>
                        <!-- /.card-header -->

                        <div class="card-body">
                            @if(in_array($viewType, ['show']))
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="province_id">{{ __('general.province') }} <span
                                                    class="text-red">*</span></label>
                                            <select name="province_id" id="province_id"
                                                    class="form-control input-lg select2" disabled>
                                                @if(isset($province))
                                                    <option value="{{$province->id}}" selected
                                                            disabled>{{$province->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="city_id">{{ __('general.city') }} <span
                                                    class="text-red">*</span></label>
                                            <select name="city_id" id="city_id" class="form-control select2 city"
                                                    disabled>
                                                @if(isset($city))
                                                    <option value="{{$city->id}}">{{$city->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="district_id">{{ __('general.district') }} <span
                                                    class="text-red">*</span></label>
                                            <select name="district_id" id="district_id"
                                                    class="form-control select2 district" disabled>
                                                @if(isset($district))
                                                    <option value="{{$district->id}}">{{$district->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sub_district_id">{{ __('general.sub_district') }} <span
                                                    class="text-red">*</span></label>
                                            <select name="sub_district_id" id="sub_district_id"
                                                    class="form-control select2 sub_district" disabled>
                                                @if(isset($subDistrict))
                                                    <option value="{{$subDistrict->id}}">{{$subDistrict->name}}</option>
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Form::text('zip_code', old('zip_code', isset($dataUser->zip_code) ? $dataUser->zip_code : null), array_merge(['class' => $errors->has('zip_code') ? 'form-control' : 'form-control', 'id' => 'zip_code', 'required' => true, 'placeholder' => 'Masukan kode pos ', 'autocomplete' => 'off'], $addAttribute)) }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Form::text('address', old('address', isset($dataUser->address) ? $dataUser->address : null), array_merge(['class' => $errors->has('address') ? 'form-control' : 'form-control', 'id' => 'address', 'required' => true, 'placeholder' => 'e.g Rumah, Kantor', 'autocomplete' => 'off'], $addAttribute ))}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="address_detail">{{ __('general.address_detail') }} <span
                                                    class="text-red">*</span></label>
                                            {{ Form::textarea('address_detail', old('address_detail', isset($dataUser->address_detail) ? $dataUser->address_detail : null), array_merge(['class' => $errors->has('address_detail') ? 'ckeditor' : 'ckeditor', 'id' => 'address_detail', 'required' => true, 'autocomplete' => 'off'], $addAttribute))}}
                                        </div>
                                    </div>
                                </div>


                            @endif
                        </div>
                        <!-- /.card-body -->

                    </div>
                @endif

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

                @if (in_array($viewType, ['show']) && $data->transaction_id > 0)
                    <a href="<?php echo route('admin.transaction-lab.show', $data->transaction_id) ?>"
                       class="mb-2 mr-2 btn btn-info" title="{{ __('general.transaction') }}">
                        <i class="fa fa-shopping-cart"></i><span class=""> {{ __('general.transaction') }}</span>
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
@stop
