<?php
$klinik_id = app()->request->get('filter_klinik_id');
$category = app()->request->get('filter_category');

$params = [];
if ($klinik_id) {
    $params['filter_klinik_id'] = $klinik_id;
}
if ($category) {
    $params['filter_category'] = $category;
}
?>
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
            <div class="card">
                @if ($permission['create'])
                    <div class="card-header">
                        <a href="<?php echo route('admin.' . $thisRoute . '.create') ?>" class="mb-2 mr-2 btn btn-success"
                           title="@lang('general.create')">
                            <i class="fa fa-plus-square"></i> @lang('general.create')
                        </a>
                        <a href="<?php echo route('admin.' . $thisRoute . '.create2') ?>" class="mb-2 mr-2 btn btn-primary"
                           title="@lang('general.import')">
                            <i class="fa fa-file-excel-o"></i> @lang('general.import')
                        </a>
                      </div>
                @endif
                    <form method="get">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="filter_klinik_id">{{ __('general.klinik') }}</label>
                                    {{ Form::select('filter_klinik_id', $listSet['klinik_id'], old('filter_klinik_id', $klinik_id), ['class' => 'form-control select2', 'autocomplete' => 'off']) }}
                                </div>
                                <div class="col-md-4">
                                    <label for="filter_category">{{ __('general.product_category') }}</label>
                                    {{ Form::select('filter_category', $listSet['filter_category'], old('filter_category', $category), ['class' => 'form-control select2', 'autocomplete' => 'off']) }}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <br/>
                                    <button class="mb-1 btn btn-primary btn-sm" type="submit"
                                            title="@lang('general.filter')">
                                        <i></i>
                                        @lang('general.filter')
                                    </button>

                                    <a href="<?php echo route('admin.' . $thisRoute . '.index') ?>" class="mb-1 btn btn-warning btn-sm" type="submit"
                                       title="@lang('general.reset')">
                                        <i></i>
                                        @lang('general.reset')
                                    </a>

                                </div>
                            </div>
                        </div>
                    </form>
                <!-- /.card-header -->
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="data1">
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </section>
@stop

@section('script-bottom')
    @parent
    <script type="text/javascript">
        'use strict';
        let table;
        table = jQuery('#data1').DataTable({
            serverSide: true,
            processing: true,
            autoWidth: false,
            scrollX: true,
            // pageLength: 25,
            // lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            ajax: '{{ route('admin.' . $thisRoute . '.dataTable') }}{!! '?' . http_build_query($params) !!}',
            aaSorting: [ {!! isset($listAttribute['aaSorting']) ? $listAttribute['aaSorting'] : "[0,'desc']" !!}],
            columns: [
                    @foreach($passing as $fieldName => $fieldData)
                {data: '{{ $fieldName }}', title: "{{ __($fieldData['lang']) }}" <?php echo strlen($fieldData['custom']) > 0 ? $fieldData['custom'] : ''; ?> },
                @endforeach
            ],
            fnDrawCallback: function( oSettings ) {
                // $('a[data-rel^=lightcase]').lightcase();
            }
        });

        function actionData(link, method) {

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

                    },
                    complete: function(){
                        table.ajax.reload();
                    }
                });
            }
        }

    </script>
@stop
