<?php
$dosis = isset($listProduct['dosis']) ? $listProduct['dosis'] : null;
$information = isset($listProduct['information']) ? $listProduct['information'] : null;
$indication = isset($listProduct['indication']) ? $listProduct['indication'] : null;
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
                    @if(in_array($viewType, ['create','edit','show']) )
                    <label>Upload File</label>
                      <br/>
                      <div id="list_other1">
                                <div class="d-flex align-items-center">
                                    <div class="p-2">
                                        <input type="file" name="image" class="dropify" 
                                               data-allowed-file-extensions="jpg jpeg png" accept="image/png, image/gif, image/jpeg" data-max-file-size="10M" required>
                                    </div>
                                </div>
                            </div>
                        
                        <div class="form-group">
                            <label for="information">{{ __('general.information') }}</label>
                            {{ Form::textarea('information', $information, ['id' => 'information', 'class' => 'texteditor', 'placeholder' => __('general.information')]) }}
                        </div>
                        <div class="form-group">
                            <label for="indication">{{ __('general.indication') }}</label>
                            {{ Form::textarea('indication', $indication, ['id' => 'indication', 'class' => 'texteditor', 'placeholder' => __('general.indication')]) }}
                        </div>
                        <div class="form-group">
                            <label for="dosis">{{ __('general.dosis') }}</label>
                            {{ Form::textarea('dosis', $dosis, ['id' => 'dosis', 'class' => 'texteditor', 'placeholder' => __('general.indication')]) }}
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
    <script src="{{ asset('/assets/cms/js/ckeditor/ckeditor.js') }}"></script>
    <script>  
    
    let setIndex1 = 1;

     

        $(document).ready(function() {
            $('.dropify').dropify();
            
        });
        
     

        function add_other1() {
        let html = '<div class="d-flex align-items-center">' +
        '<div class="p-2">' +
        '<input type="file" id="upload_file_pemuktahiran_' + setIndex1 +'" name="upload_file_pemuktahiran[]" class="dropify" accept=".pdf"' +
        ' data-allowed-file-extensions="pdf" data-max-file-size="10M">' +
        '</div>' +
        '<div class="p-2">' +
        '<a href="#" onclick="return remove_other(this)">{!! __('general.delete') !!}</a>' +
        '</div>' +
        '</div>';

            $('#list_other1').append(html);
            $('#upload_file_pemuktahiran_' + setIndex1).dropify();

            setIndex1++;

            return false;

            }
    function remove_other(curr) {
            $(curr).parent().parent().remove();
            return false;
        }


     </script>   
@stop
