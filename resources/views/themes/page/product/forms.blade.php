<?php
$title = isset($listProduct['title']) ? $listProduct['title'] : null;
$desc = isset($listProduct['desc']) ? $listProduct['desc'] : null;


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
                    @if(in_array($viewType, ['show','edit']) )
                    <?php $no = 0; ?>
                    @foreach($title as $title)
                    <?php $no++; ?>
                    <b>Title - {!! $no !!}</b>
                    {{ Form::text('title', $title, array_merge(['id' => 'desc','name'=>'title[]', 'class' => 'form-control', 'placeholder' => __('general.title')], $addAttribute)) }}
                    <br>
                    @endforeach
                    <br>
                    <?php  $no = 0; ?>
                    @foreach($desc as $desc)
                    <?php $no++; ?>
                    <b>Desc - {!! $no !!}</b>
                    <br>
                    {{ Form::textarea('desc', $desc, array_merge(['id' => 'desc', 'name'=>'desc[]', 'class' => 'editor', 'placeholder' => __('general.information')], $addAttribute)) }}
                    <br>
                    @endforeach
                    @endif
                    @if(in_array($viewType, ['create','edit']) )
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

                       <div id="list_desc">
                           <div class="form-group">
                               <label for="desc">{{ __('general.desc') }}</label>
                               {{ Form::text('title', old('title'), ['id' => 'desc', 'name'=>'title[]', 'class' => 'form-control', 'placeholder' => __('general.title')]) }}
                               <br>
                               {{ Form::textarea('desc', old('desc'), ['id' => 'desc', 'name'=>'desc[]', 'class' => 'editor', 'placeholder' => __('general.information')]) }}
                               <br>
                               <a href="#" onclick="return add_desc1()" class="btn btn-warning">Tambah</a>
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
    <script src="{{ asset('/assets/cms/js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('/assets/cms/js/ckeditor/adapters/jquery.js') }}"></script>
    <script>

    let setIndex1 = 1;



        $(document).ready(function() {
            $('.dropify').dropify();
            $( 'textarea.editor' ).ckeditor();
        });




        function add_desc1() {
        let html = '<div class="form-group">' +
        '<input type="text" id="title_' + setIndex1 +'" name="title[]" class="form-control" placeholder="title"> ' +
        '<br>'+
        '<textarea id="desc_' + setIndex1 +'" name="desc[]" class="editor"> ' +
        '</textarea>' +
        '<div class="p-2">' +
        '<a href="#" onclick="return remove_other(this)" style="color:red;">&nbsp;<i class="nav-icon fa fa-trash">{!! __('general.delete') !!}</i></a>' +
        '</div>' +
        '</div>';

            $('#list_desc').append(html);
            $('#desc_' + setIndex1).ckeditor();

            setIndex1++;

            return false;

            }
            function remove_other(curr) {
            $(curr).parent().parent().remove();
            return false;
            }


     </script>
@stop
