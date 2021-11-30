<?php
$interval = app()->request->get('interval');
$timeStart = app()->request->get('time_start');
$serviceId = app()->request->get('service_id');

if(!$interval || $interval == 00) {
    $interval = 15;
}

if(!$timeStart) {
    $timeStart = '07:00';
}
?>

@extends(env('ADMIN_TEMPLATE').'._base.layout')

@section('title', __('general.title_home', ['field' => $thisLabel]))

@section('css')
    @parent
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- fullCalendar -->
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
                        <li class="breadcrumb-item"><a href="<?php echo route('admin') ?>"><i class="fa fa-dashboard"></i> {{ __('general.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('general.title_home', ['field' => $thisLabel]) }}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card"

                <!-- /.card-header -->
                <div class="card-header" style="background-color: #2e6da4; color:white;">
                 <div class="row">
                     <div class="col-md-4">
                         <div class="form-group">
                         <label for="filter_interval">{{ __('general.interval') }}</label>
                         <input style="margin-left: 10px;" type="text" class="form-control-sm center" id="set_interval" name="set_interval" value="{{ $interval }}" autocomplete="off"  required>
                         </div>
                     </div>
                     <div class="col-md-4">
                         <div class="form-group">
                         <label for="time_start">{{ __('general.time_start') }}</label>
                         <input style="margin-left: 10px;" type="text" class="form-control-sm center" id="time_start" name="time_start" value="{{ $timeStart }}" autocomplete="off" required>
                         </div>
                     </div>
                     <div class="col-md-4 right">
                         <div class="form-group">
                         <label for="service">{{ __('general.service') }}</label>
                         {{ Form::select('service_id', $listSet['service_id'], old('service_id', $serviceId), ['style' => 'margin-left: 10px;', 'id' => 'service_id', 'class' => 'form-control-sm', 'autocomplete' => 'off']) }}
                         </div>
                     </div>
                 </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                    <div id="calendar"></div>
                    <div class="loading_start" style="position: absolute;top: 75px;left: 0;width: 100%;height: 100%;background: rgba(255,255,255,0.8);z-index: 1;text-align: center;display: none;">
                        <div style="position: relative;top: 15%;"><i class="fa fa-spin fa-5x fa-refresh"></i></div>
                    </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </section>
@stop

@section('script-bottom')
    @parent
    <!-- fullCalendar 2.2.5 -->

    <script type="text/javascript">
        let interval = "<?= $interval ?>";
        let time_start = "<?= $timeStart ?>";
        let service_id = "<?= $serviceId ?>";

        $(document).ready(function (){
            let listColor = <?php echo json_encode(get_list_appointment_color()) ?>;
            $('#set_interval').datetimepicker({
                format: 'mm',
                stepping: 15,
            });

            $('#time_start').datetimepicker({
                format: 'HH:mm',
                stepping: 15
            });

            $.ajaxSetup({
                headers:{
                    'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')
                }
            });

            var calendar = $('#calendar').fullCalendar({
                //default : 'agendaWeek',
                editable: false,
                defaultView: 'agendaDay',
                slotDuration: '00:'+interval+':00',
                minTime: time_start + ':00', // Start time for the calendar
                maxTime: '18:00:00', // End time for the calendar
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },

                events:  function(start_time, end_time, timezone, callback){
                    //$('.loading_start').show();
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('admin.appointmentLabSchedule') }}",
                        data: {
                            service_id : service_id
                        },
                        success: function (response){
                            var events = [];

                            $.each(response, function (index, item){
                                var getStatus = parseInt(item.status);
                                var color = listColor[getStatus];

                                events.push({
                                    id: item.id,
                                    title: item.code+' - '+item.patient+' - '+item.lab_name,
                                    start: item.time_start,
                                    end: item.time_end,
                                    backgroundColor: color,
                                    borderColor: color,
                                });
                            });
                            callback(events);
                            //$('.loading_start').hide();
                        }
                    });
                },
                selectable: false,
                selectHelper: true,
            });

        });

        $('#set_interval').on('focusout', function() {
            let interval = $(this).val();
            let time_start = $('#time_start').val();
            let service_id = $('#service_id').val();
            window.location.href = "?interval=" + interval + '&time_start=' + time_start + '&service_id=' + service_id;
        });

        $('#time_start').on('focusout', function() {
            let interval = $('#set_interval').val();
            let time_start = $(this).val();
            let service_id = $('#service_id').val();
            window.location.href = "?interval=" + interval + '&time_start=' + time_start + '&service_id=' + service_id;
        });

        $('#service_id').on('change', function() {
            let interval = $('#set_interval').val();
            let time_start = $('#time_start').val();
            let service_id = $(this).val();
            window.location.href = "?interval=" + interval + '&time_start=' + time_start + '&service_id=' + service_id;
        });
    </script>
@stop
