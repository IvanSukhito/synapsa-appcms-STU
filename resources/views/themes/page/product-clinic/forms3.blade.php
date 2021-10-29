<?php
switch ($viewType) {
    case 'edit': $printCard = 'card-success'; break;
    case 'create': $printCard = 'card-primary'; break;
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
                    <h3 class="card-title">{{ __('general.search_product_from_synapsa') }} </h3>
                </div>
                <!-- /.card-header -->

                @if(in_array($viewType, ['create']))
                    {{ Form::open(['route' => ['admin.' . $thisRoute . '.create3'], 'method' => 'GET', 'files' => true, 'id'=>'form', 'role' => 'form'])  }}
                @elseif(in_array($viewType, ['edit']))
                    {{ Form::open(['route' => ['admin.' . $thisRoute . '.update', $data->{$masterId}], 'method' => 'PUT', 'files' => true, 'id'=>'form', 'role' => 'form'])  }}
                @else
                    {{ Form::open(['id'=>'form', 'role' => 'form'])  }}
                @endif

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category_product">{{ __('general.product-category') }} <span class="text-red">*</span></label>
                                <select name="category" id="category" class="form-control input-lg select2" required>
                                    <option value="#" readonly="readonly">Category Product</option>
                                    @foreach($category as $list)
                                        <option value="{{$list->id}}">{{$list->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="product">{{ __('general.product') }} <span class="text-red">*</span></label>
                                <select name="product" id="product" class="form-control select2 product" required>
                                    <option value="">Product</option>
                                </select>
                            </div>
                        </div>


                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">

                    @if(in_array($viewType, ['create']))
                        <button type="button" name="apply" id="apply" class="mb-2 mr-2 btn btn-primary" title="@lang('general.apply')" data-link="{{ route('admin.' . $thisRoute . '.create3') }}">
                            <i class="fa fa-save"></i><span class=""> @lang('general.apply')</span>
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
            $('#category').change(function() {

                let category = $('#category').val();

                var div= $('#product').parent();
                var op="";

                $.ajax({
                    type: 'GET',
                    url: "{{ route('admin.findProductSynapsa') }}",
                    data: { category :category},
                    success : function (data){

                        op+='<option value="0" selected disabled>Chose Product</option>';
                        for(var i=0;i<data.length;i++){
                            op+='<option value="'+data[i].id+'">'+data[i].name+'</option>';
                        }

                        div.find('#product').html(" ");
                        div.find('#product').append(op);

                    },
                    error: function (){

                    }
                })
            });

                $('#apply').click(function (){
                    var getLink = $(this).data('link');
                    var getValue = $('#product').val();
                    var url = getLink + '?id=' + getValue;
                    console.log(url);
                    window.location.href=url;
                });

        });

        function changeURL(curr) {
            let getLink = $(curr).data('link');
            let getValue = $(curr).val();
            window.location = getLink + '?date=' + getValue;
        }
    </script>
@stop
