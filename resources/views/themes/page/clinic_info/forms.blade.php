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
                    @if(in_array($viewType, ['edit']))
                        @include(env('ADMIN_TEMPLATE').'._component.generate_forms')
                        <div class="form-group">
                        <label for="monday">{{ __('general.monday') }} <span class="text-red">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend datepicker-trigger">
                                <div class="input-group-text">
                                    <i class="fa fa-calendar"></i>
                                </div>
                            </div>
                            {{ Form::text('monday', old('monday', isset($data->monday) ? $data->monday : null), ['class' => $errors->has('monday') ? 'form-control pull-right timerange is-invalid' : 'form-control pull-right timerange', 'id' => 'monday', 'required' => true, 'autocomplete' => 'off']) }}
                        </div>
                        <label for="mondaycheck">Closed</label>
                        <input type="checkbox" id="mondaycheck" name="mondaycheck" @if(in_array($viewType, ['show'])) disabled @endif>
                </div>

                <div class="form-group">
                    <label for="tuesday">{{ __('general.tuesday') }} <span class="text-red">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend datepicker-trigger">
                            <div class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        {{ Form::text('tuesday', old('tuesday', isset($data->tuesday) ? $data->tuesday : null), ['class' => $errors->has('tuesday') ? 'form-control pull-right timerange is-invalid' : 'form-control pull-right timerange', 'id' => 'tuesday', 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                    <label for="tuesdaycheck">Closed</label>
                    <input type="checkbox" id="tuesdaycheck" name="tuesdaycheck" @if(in_array($viewType, ['show'])) disabled @endif>
                </div>
                <div class="form-group">
                    <label for="wednesday">{{ __('general.wednesday') }} <span class="text-red">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend datepicker-trigger">
                            <div class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        {{ Form::text('wednesday', old('wednesday', isset($data->wednesday) ? $data->wednesday : null), ['class' => $errors->has('wednesday') ? 'form-control pull-right timerange is-invalid' : 'form-control pull-right timerange', 'id' => 'wednesday', 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                    <label for="wednesdaycheck">Closed</label>
                    <input type="checkbox" id="wednesdaycheck" name="wednesdaycheck" @if(in_array($viewType, ['show'])) disabled @endif>
                </div>

                <div class="form-group">
                    <label for="thursday">{{ __('general.thursday') }} <span class="text-red">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend datepicker-trigger">
                            <div class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        {{ Form::text('thursday', old('thursday', isset($data->thursday) ? $data->thursday : null), ['class' => $errors->has('thursday') ? 'form-control pull-right timerange is-invalid' : 'form-control pull-right timerange', 'id' => 'thursday', 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                    <label for="thursdaycheck">Closed</label>
                    <input type="checkbox" id="thursdaycheck" name="thursdaycheck" @if(in_array($viewType, ['show'])) disabled @endif>
                </div>

                <div class="form-group">
                    <label for="friday">{{ __('general.friday') }} <span class="text-red">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend datepicker-trigger">
                            <div class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        {{ Form::text('friday', old('friday', isset($data->friday) ? $data->friday : null), ['class' => $errors->has('friday') ? 'form-control pull-right timerange is-invalid' : 'form-control pull-right timerange', 'id' => 'friday', 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                    <label for="fridaycheck">Closed</label>
                    <input type="checkbox" id="fridaycheck" name="fridaycheck" @if(in_array($viewType, ['show'])) disabled @endif>
                </div>

                <div class="form-group">
                    <label for="saturday">{{ __('general.saturday') }} <span class="text-red">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend datepicker-trigger">
                            <div class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        {{ Form::text('saturday', old('saturday', isset($data->saturday) ? $data->saturday : null), ['class' => $errors->has('saturday') ? 'form-control pull-right timerange is-invalid' : 'form-control pull-right timerange', 'id' => 'saturday', 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                    <label for="saturdaycheck">Closed</label>
                    <input type="checkbox" id="saturdaycheck" name="saturdaycheck" @if(in_array($viewType, ['show'])) disabled @endif>
                </div>

                <div class="form-group">
                    <label for="sunday">{{ __('general.sunday') }} <span class="text-red">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend datepicker-trigger">
                            <div class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        {{ Form::text('sunday', old('sunday', isset($data->sunday) ? $data->sunday : null), ['class' => $errors->has('sunday') ? 'form-control pull-right timerange is-invalid' : 'form-control pull-right timerange', 'id' => 'sunday', 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                    <label for="sundaycheck">Closed</label>
                    <input type="checkbox" id="sundaycheck" name="sundaycheck" @if(in_array($viewType, ['show'])) disabled @endif>
                </div>
                    @endif

                    @if(in_array($viewType, ['show']))
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td>@lang('general.name')</td>
                                            <td>:</td>
                                            <td>{{ $data->name ?? '-'}}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.address')</td>
                                            <td>:</td>
                                            <td>{{ $data->address ?? '-'}}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.clinic_phone')</td>
                                            <td>:</td>
                                            <td>{{ '+62'.$data->no_telp ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.email')</td>
                                            <td>:</td>
                                            <td> {{ $data->email ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.monday')</td>
                                            <td>:</td>
                                            <td>{{ $data->monday?? '-'}}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.tuesday')</td>
                                            <td>:</td>
                                            <td>{{ $data->tuesday?? '-'}}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.wednesday')</td>
                                            <td>:</td>
                                            <td>{{ $data->wednesday?? '-'}}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.thursday')</td>
                                            <td>:</td>
                                            <td>{{ $data->thursday?? '-'}}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.friday')</td>
                                            <td>:</td>
                                            <td>{{ $data->friday?? '-'}}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.saturday')</td>
                                            <td>:</td>
                                            <td>{{ $data->saturday?? '-'}}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('general.sunday')</td>
                                            <td>:</td>
                                            <td>{{ $data->sunday?? '-'}}</td>
                                        </tr>
                                    </table>
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

    <script type="text/javascript">
        $(document).ready(function() {
            let monday = $('#monday').val();
            let tuesday = $('#tuesday').val();
            let wednesday = $('#wednesday').val();
            let thursday = $('#thursday').val();
            let friday = $('#friday').val();
            let saturday = $('#saturday').val();
            let sunday = $('#sunday').val();

            if(monday === 'Closed') {
                $('#mondaycheck').prop('checked', true);
                $('#monday').prop('readonly', true);
            }

            if(tuesday === 'Closed') {
                $('#tuesdaycheck').prop('checked', true);
                $('#tuesday').prop('readonly', true);
            }

            if(wednesday === 'Closed') {
                $('#wednesdaycheck').prop('checked', true);
                $('#wednesday').prop('readonly', true);
            }

            if(thursday === 'Closed') {
                $('#thursdaycheck').prop('checked', true);
                $('#thursday').prop('readonly', true);
            }

            if(friday === 'Closed') {
                $('#fridaycheck').prop('checked', true);
                $('#friday').prop('readonly', true);
            }

            if(saturday === 'Closed') {
                $('#saturdaycheck').prop('checked', true);
                $('#saturday').prop('readonly', true);
            }

            if(sunday === 'Closed') {
                $('#sundaycheck').prop('checked', true);
                $('#sunday').prop('readonly', true);
            }
        });

        $('#mondaycheck').change(function() {
            if($('#mondaycheck').prop('checked') === true) {
                $('#monday').val('Closed');
                $('#monday').prop('readonly', true);
            }
            else {
                $('#monday').val('00:00 - 23:59');
                $('#monday').prop('readonly', false);
            }
        });

        $('#tuesdaycheck').change(function() {
            if($('#tuesdaycheck').prop('checked') === true) {
                $('#tuesday').val('Closed');
                $('#tuesday').prop('readonly', true);
            }
            else {
                $('#tuesday').val('00:00 - 23:59');
                $('#tuesday').prop('readonly', false);
            }
        });

        $('#wednesdaycheck').change(function() {
            if($('#wednesdaycheck').prop('checked') === true) {
                $('#wednesday').val('Closed');
                $('#wednesday').prop('readonly', true);
            }
            else {
                $('#wednesday').val('00:00 - 23:59');
                $('#wednesday').prop('readonly', false);
            }
        });

        $('#thursdaycheck').change(function() {
            if($('#thursdaycheck').prop('checked') === true) {
                $('#thursday').val('Closed');
                $('#thursday').prop('readonly', true);
            }
            else {
                $('#thursday').val('00:00 - 23:59');
                $('#thursday').prop('readonly', false);
            }
        });

        $('#fridaycheck').change(function() {
            if($('#fridaycheck').prop('checked') === true) {
                $('#friday').val('Closed');
                $('#friday').prop('readonly', true);
            }
            else {
                $('#friday').val('00:00 - 23:59');
                $('#friday').prop('readonly', false);
            }
        });

        $('#saturdaycheck').change(function() {
            if($('#saturdaycheck').prop('checked') === true) {
                $('#saturday').val('Closed');
                $('#saturday').prop('readonly', true);
            }
            else {
                $('#saturday').val('00:00 - 23:59');
                $('#saturday').prop('readonly', false);
            }
        });

        $('#sundaycheck').change(function() {
            if($('#sundaycheck').prop('checked') === true) {
                $('#sunday').val('Closed');
                $('#sunday').prop('readonly', true);
            }
            else {
                $('#sunday').val('00:00 - 23:59');
                $('#sunday').prop('readonly', false);
            }
        });
    </script>
@stop
