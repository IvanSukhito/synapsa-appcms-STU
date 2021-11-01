<?php
$title = isset($listSettings['title']) ? $listSettings['title'] : null;
$desc = isset($listSettings['description']) ? $listSettings['description'] : null;


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

                    <div class="form-group">
                        <h5>{{ __('general.step') }}</h5>
                        <hr/>
                    </div>
                    <div id="list_desc">
                    @if(in_array($viewType, ['create']) )
                       <div>
                           <div class="form-group">
                               <label for="title_0">{{ __('general.title') }} 1</label>
                               {{ Form::text('title[0]', old('title'), ['id' => 'title_0', 'name'=>'title[]', 'class' => 'form-control', 'placeholder' => __('general.title').' 1']) }}
                           </div>
                           <div class="form-group">
                               <label for="desc_0">{{ __('general.desc') }} 1</label>
                               {{ Form::textarea('desc[0]', old('desc'), ['id' => 'desc_0', 'name'=>'desc[]', 'class' => 'form-control editor']) }}
                           </div>
                           <hr/>
                       </div>
                    @else
                        @foreach($listSettings as $index => $list)
                            <div>
                                <div class="form-group">
                                    <label for="title_{{$index}}">{{ __('general.title') }} {{$index+1}}</label>
                                    {{ Form::text('title['.$index.']', $list['title'], ['id' => 'title_'.$index, 'name'=>'title['.$index.']',
                                    'class' => 'form-control', 'placeholder' => __('general.title').' '.($index+1), 'disabled' => $viewType == 'show' ? true : false]) }}
                                </div>
                                <div class="form-group">
                                    <label for="desc_{{$index}}">{{ __('general.desc') }} {{$index+1}}</label>
                                    {{ Form::textarea('desc['.$index.']', $list['description'], ['id' => 'desc_'.$index, 'name'=>'desc['.$index.']',
                                    'class' => 'form-control editor', 'disabled' => $viewType == 'show' ? true : false]) }}
                                </div>
                                @if($index > 0 && $viewType != 'show')
                                    <div class="p-2">
                                        <a href="#" data-id="{{$index}}" class="text-danger" onclick="return remove_desc(this)"><i class="nav-icon fa fa-trash"> {!! __('general.delete') !!}</i></a>
                                    </div>
                                @endif
                                <hr/>
                            </div>
                        @endforeach
                    @endif
                    </div>
                    @if(in_array($viewType, ['create', 'edit']))
                        <div class="form-group">
                            <a href="#" onclick="return add_desc()" class="btn btn-warning">Tambah</a>
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
    <!--<script src="{{ asset('/assets/cms/js/ckeditor/ckeditor.js') }}"></script>-->
    <script type="text/javascript">
        'use strict';

        let setIndex1 = parseInt('{{ isset($listSettings) ? count($listSettings) : 0 }}');

        $(document).ready(function() {
            $('.dropify').dropify();

            $('.editor').each(function(i, item) {
                CKEDITOR.replace(item.id, {
                    autoParagraph: true,
                    allowedContent: true,
                    extraAllowedContent: '*(*);*{*};*[*]{*};div(class);span(class);h5[*]',
                    extraPlugins: 'justify,format,colorbutton,font,smiley'
                });
            });

        });

        function add_desc() {
            setIndex1++;
            let setNumber = setIndex1 + 1;
            let html = '<div><div class="form-group">' +
                '<label for="title_' + setIndex1 + '">{{ __('general.title') }} ' + setNumber + '</label>' +
                '<input type="text" id="title_' + setIndex1 + '" name="title['+setIndex1+']" class="form-control" placeholder="Title '+setNumber+'"> ' +
                '</div><div class="form-group">' +
                '<label for="desc_' + setIndex1 + '">{{ __('general.desc') }} ' + setNumber + '</label>' +
                '<textarea id="desc_' + setIndex1 + '" name="desc['+setIndex1+']" class="form-control editor"> ' +
                '</textarea>' +
                '</div>' +
                '<div class="p-2">' +
                '<a href="#" data-id="' + setIndex1 +'" class="text-danger" onclick="return remove_desc(this)"><i class="nav-icon fa fa-trash"> {!! __('general.delete') !!}</i></a>' +
                '</div>' +
                '</div><hr/></div>';

            $('#list_desc').append(html);

            CKEDITOR.replace('desc_' + setIndex1, {
                autoParagraph: true,
                allowedContent: true,
                extraAllowedContent: '*(*);*{*};*[*]{*};div(class);span(class);h5[*]',
                extraPlugins: 'justify,format,colorbutton,font,smiley'
            });

            return false;

        }

        function remove_desc(curr) {
            $(curr).parent().parent().remove();
            return false;
        }

     </script>
@stop
