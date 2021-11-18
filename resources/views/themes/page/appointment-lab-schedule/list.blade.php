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
                         <label for="filter_interval">{{ __('general.interval') }}</label>
                         <input style="margin-left: 20px;" type="text" class="form-control-sm center" id="set_interval" name="set_interval" autocomplete="off"  required>
                     </div>
                     <div class="col-md-4">
                         <label for="filter_time_start">{{ __('general.time_start') }}</label>
                         <input style="margin-left: 20px;"  type="text" class="form-control-sm center" id="time_start" name="time_start" autocomplete="off" required>
                     </div>
                     <div class="col-md-4 right">
                         <label for="filter_service">{{ __('general.service') }}</label>
                         <input style="margin-left: 20px;"  type="text" class="form-control-sm center" id="time_start" name="time_start" autocomplete="off" required>
                     </div>
                 </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
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

        $(document).ready(function (){

            $('#set_interval').datetimepicker({
                format: 'mm',
                stepping: 15
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
                defaultView: 'agendaWeek',
                slotDuration: '00:30:00',
                minTime: '06:30:00', // Start time for the calendar
                maxTime: '18:00:00', // End time for the calendar
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek,agendaDay'
                },

                events: '/admin/appointment-lab-schedule',
                selectable: true,
                selectHelper: true,
            });

        });

        $('#set_interval').on('focusout', function() {
            let interval = $(this).val();
            console.log(interval);
        });



    </script>
@stop
