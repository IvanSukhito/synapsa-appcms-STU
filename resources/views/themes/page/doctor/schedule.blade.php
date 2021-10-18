@extends(env('ADMIN_TEMPLATE').'._base.layout')

@section('title', __('general.title_home', ['field' => $thisLabel]))

@section('css')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('general.title_home', ['field' => $thisLabel]) }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo route('admin.profile.index') ?>"><i class="fa fa-user"></i> {{ __('general.profile') }}</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo route('admin.' . $thisRoute . '.index') ?>"> {{ __('general.title_home', ['field' => $thisLabel]) }}</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo route('admin.' . $thisRoute . '.show', $getDoctor->id) ?>"> {{ __('general.title_home', ['field' => $getDoctor->name]) }}</a></li>
                        <li class="breadcrumb-item active">{{ __('general.title_home', ['field' => $thisLabel]) }}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                @if ($permission['create'])
                    <div class="card-header">
                        <a href="#" class="mb-2 mr-2 btn btn-success" onclick="createForm()"
                           title="@lang('general.create')">
                            <i class="fa fa-plus-square"></i> @lang('general.create')
                        </a>
                    </div>
                @endif

                <!-- /.card-header -->
                <div class="card-header">

                    <div id="list-day">
                        {{ Form::select('list_date', $getListDate, $getTargetDate, ['id' => 'list_date',
                            'class' => 'form-control', 'onchange' => 'changeDate(this)', 'data-link' => route('admin.' . $thisRoute . '.schedule', ['id' => $getDoctor->id])
                            ]) }}
                    </div>

                </div>
                <div class="card-header">
                    <h3 class="card-title">{{ __('general.title_home', ['field' => $thisLabel]) }}: {{ date('d-F-Y', strtotime($getTargetDate)) }}</h3>
                </div>
                <div class="card-body">

                    <div id="list_schedule">
                        @foreach($getData as $list)

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3">
                                            <div class="form-group">
                                                <div class="form-group">
                                                    <label for="service_{!! $list->id !!}">{{ __('general.service') }} <span class="text-red">*</span></label>
                                                    {{ Form::select('service_'.$list->id, $listSet['service_id'], $list->service_id, ['id' => 'service_'.$list->id, 'class' => 'form-control', 'required' => true]) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-group">
                                                <label for="date_{!! $list->id !!}">@lang('general.date') <span class="text-red">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend datepicker-trigger">
                                                        <div class="input-group-text">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                    </div>
                                                    {{ Form::text('date_'.$list->id, $list->date_available, ['id' => 'date_'.$list->id, 'class' => 'form-control', 'required' => true, 'autocomplete'=>'off']) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label for="time_start_{!! $list->id !!}">@lang('general.time_start') <span class="text-red">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend datepicker-trigger">
                                                        <div class="input-group-text">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                    </div>
                                                    {{ Form::text('time_start_'.$list->id, $list->time_start, ['id' => 'time_start_'.$list->id, 'class' => 'form-control', 'required' => true, 'autocomplete'=>'off']) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label for="time_end_{!! $list->id !!}">@lang('general.time_end') <span class="text-red">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend datepicker-trigger">
                                                        <div class="input-group-text">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                    </div>
                                                    {{ Form::text('time_end_'.$list->id, $list->time_end, ['id' => 'time_end_'.$list->id, 'class' => 'form-control', 'required' => true, 'autocomplete'=>'off']) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                @if ($permission['edit'])
                                                <a href="#" class="mb-1 btn btn-primary btn-sm" title="@lang('general.update')"
                                                   data-href="{{ route('admin.' . $thisRoute . '.updateSchedule', ['id' => $list->doctor_id, 'scheduleId' => $list->id]) }}"
                                                   data-id="{!! $list->id !!}"
                                                   onclick="return updateData(this)">
                                                    <i class="fa fa-pencil"></i>
                                                    <span class="d-none d-md-inline"> @lang('general.update')</span>
                                                </a>
                                                @endif
                                                @if ($permission['destroy'])
                                                <a href="#" class="mb-1 btn btn-danger btn-sm" title="@lang('general.delete')"
                                                   onclick="return actionData('{{ route('admin.' . $thisRoute . '.destroySchedule', ['id' => $list->doctor_id, 'scheduleId' => $list->id]) }}', 'delete', this)">
                                                    <i class="fa fa-trash"></i>
                                                    <span class="d-none d-md-inline"> @lang('general.delete')</span>
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endforeach
                    </div>

                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </section>

    <div class="modal" tabindex="-1" role="dialog" id="scheduleTask">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="scheduleForm" action="{!! route('admin.'.$thisRoute.'.storeSchedule', $getDoctor->id) !!}" enctype="multipart/form-data" method="POST" onsubmit="return submitScheduleForm(this)">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="schedule-title">@lang('general.title_create', ['field' => $thisLabel])</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="form-group">
                                <label for="service">{{ __('general.service') }} <span class="text-red">*</span></label>
                                {{ Form::select('service', $listSet['service'], old('service'), ['id' => 'service', 'class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="date">@lang('general.date') <span class="text-red">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend datepicker-trigger">
                                    <div class="input-group-text">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                </div>
                                <input type="text" class="form-control" id="date" name="date" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="time_start">@lang('general.time_start') <span class="text-red">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend datepicker-trigger">
                                    <div class="input-group-text">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                </div>
                                <input onfocusout="return setTimeEnd(this)" type="text" class="form-control" id="time_start" name="time_start" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="time_end">@lang('general.time_end') <span class="text-red">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend datepicker-trigger">
                                    <div class="input-group-text">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                </div>
                                <input type="text" class="form-control" id="time_end" name="time_end" autocomplete="off" required>
                            </div>
                        </div>
                        <span class="small">Notes:<br />Telemed: 20 minutes video call, 10 minutes diagnosa</span>
                        <div class="form-group text-red" id="errorForm">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary pull-left" data-dismiss="modal">@lang('general.close')</button>
                        <button type="submit" class="btn btn-primary">@lang('general.submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

@section('script-bottom')
    @parent
    <script type="text/javascript">
        'use strict';

        $(document).ready(function() {
            $('#date').datetimepicker({
                format: 'YYYY-MM-DD',
            });
            $('#time_start').datetimepicker({
                format: 'HH:mm:ss',
                stepping: 15
            });
            // $('#time_end').datetimepicker({
            //     format: 'HH:mm:ss',
            //     stepping: 15
            // });
        });

        $('#service').on('change', function() {
            let service = $('#service').val();
            if(service === "1") {
                if($('#time_start').val().length > 0) {
                    let time = moment($('#time_start').val(), 'HH:mm:ss');
                    time = time.add(30, 'minutes').format('HH:mm:ss');

                    $('#time_end').attr('readonly', true);
                    $('#time_end').val(time);
                }
            }
            else {
                $('#time_end').attr('readonly', false);
            }
        });

        function setTimeEnd(curr) {
            let service = $('#service').val();
            console.log(typeof service);
            if(service === "1") {
                let time = moment($('#time_start').val(), 'HH:mm:ss');
                time = time.add(30, 'minutes').format('HH:mm:ss');

                $('#time_end').attr('readonly', true);
                $('#time_end').val(time);
            }
            else {
                $('#time_end').attr('readonly', false);
            }
        }

        function changeDate(curr) {
            let getLink = $(curr).data('link');
            let getValue = $(curr).val();
            window.location = getLink + '?date=' + getValue;
        }

        function createForm() {
            $('#errorForm').empty();
            $('#service').val('');
            $('#date').val('');
            $('#time_start').val('');
            $('#time_end').val('');
            $('#scheduleTask').modal('show');
        }

        function submitScheduleForm(curr) {
            $('#errorForm').empty();

            let getLink = $(curr).attr('action');
            let linkSplit = getLink.split('/');
            let url = '';
            for(let i=3; i<linkSplit.length; i++) {
                url += '/'+linkSplit[i];
            }

            let data = {
                service: $('#service').val(),
                date: $('#date').val(),
                time_start: $('#time_start').val(),
                time_end: $('#time_end').val()
            };

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                },
                success: function(result) {
                    if (parseInt(result.result) === 1) {
                        $.notify({
                            // options
                            message: result.message
                        },{
                            // settings
                            type: 'success',
                            placement: {
                                from: "bottom",
                                align: "right"
                            },
                        });

                        location.reload();

                    }
                    else {
                        $.notify({
                            // options
                            message: result.message
                        },{
                            // settings
                            type: 'danger',
                            placement: {
                                from: "bottom",
                                align: "right"
                            },
                        });
                    }
                },
                error: function(result){
                    $('#errorForm').empty();
                    if (typeof result.responseJSON.errors === 'object') {
                        $.each(result.responseJSON.errors, function(index, item) {
                            $('#errorForm').append('<div>' + item[0] + '</div>')
                        });
                    }
                },
                complete: function(result){
                }
            });

            return false;

        }

        function updateData(curr) {
            let getId = $(curr).data('id');
            let getLink = $(curr).data('href');
            let linkSplit = getLink.split('/');
            let url = '';
            for(let i=3; i<linkSplit.length; i++) {
                url += '/'+linkSplit[i];
            }

            let data = {
                service: $('#service_' + getId).val(),
                date: $('#date_' + getId).val(),
                time_start: $('#time_start_' + getId).val(),
                time_end: $('#time_end_' + getId).val()
            };

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                },
                success: function(result) {
                    if (parseInt(result.result) === 1) {
                        $.notify({
                            // options
                            message: result.message
                        },{
                            // settings
                            type: 'success',
                            placement: {
                                from: "bottom",
                                align: "right"
                            },
                        });
                    }
                    else {
                        $.notify({
                            // options
                            message: result.message
                        },{
                            // settings
                            type: 'danger',
                            placement: {
                                from: "bottom",
                                align: "right"
                            },
                        });
                    }
                },
                error: function(result){
                    if (typeof result.responseJSON.errors === 'object') {
                        $.each(result.responseJSON.errors, function(index, item) {
                            $.notify({
                                // options
                                message: item[0]
                            },{
                                // settings
                                type: 'danger',
                                placement: {
                                    from: "bottom",
                                    align: "right"
                                },
                            });
                        });
                    }
                },
                complete: function(result){
                }
            });

            return false;

        }

        function actionData(link, method, curr) {

            if(confirm('{{ __('general.ask_delete') }}')) {
                let linkSplit = link.split('/');
                let url = '';
                for(let i=3; i<linkSplit.length; i++) {
                    url += '/'+linkSplit[i];
                }

                jQuery.ajax({
                    url: url,
                    type: method,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(result) {
                        if (parseInt(result.result) === 1) {

                            $.notify({
                                // options
                                message: result.message
                            },{
                                // settings
                                type: 'success',
                                placement: {
                                    from: "bottom",
                                    align: "right"
                                },
                            });

                            $(curr).parent().parent().parent().parent().parent().remove();

                        }
                        else {
                            $.notify({
                                // options
                                message: result.message
                            },{
                                // settings
                                type: 'danger',
                                placement: {
                                    from: "bottom",
                                    align: "right"
                                },
                            });
                        }
                    },
                    error: function(result){

                    },
                    complete: function(){

                    }
                });

                return false;

            }
        }

    </script>
@stop
