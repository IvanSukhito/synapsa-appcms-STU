@extends(env('ADMIN_TEMPLATE').'._base.layout')

@section('title', __('general.dashboard'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>@lang('general.profile_perancang')</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li><a href="<?php echo route('admin') ?>"><i class="fa fa-dashboard"></i> {{ __('general.home') }}</a></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">@lang('general.profile_perancang')</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <a href="#" id="datadiri" class="col-sm-2 text-center box2">Data Diri Perancang</a>
                        <a href="#" id="pendidikan" class="active col-sm-2 text-center box2">Pendidikan Perancang</a>
                        <a href="#" id="jenjang" class="col-sm-2 text-center box2">Jenjang Perancang</a>
                        <a href="#" id="prestasi" class="col-sm-2 text-center box2">Prestasi Perancang</a>
                    </div>
                    <hr />

                    <!-- Input -->
                    <form class="form-input" method="#" action="#">

                        <div class="form-group row text-center mb-3">
                            <label for="gelar" class="col-sm-4 col-sm-offset-1">Gelar</label>
                            <div class="col-sm-7">
                                <select id="gelar" class="form-control" name="gelar">
                                    <option value="Option1">Option1</option>
                                    <option value="Option2">Option2</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="nama_universitas" class="col-sm-4 col-sm-offset-1">Nama Universitas</label>
                            <div class="col-sm-7">
                                <input id="nama_universitas" class="form-control" type="text" name="nama_universitas" placeholder="Nama Universitas">
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="masa_pendidikan1" class="col-sm-4 col-sm-offset-1">Masa Pendidikan</label>
                            <div class="col-sm-7 row">
                                <div class="col-sm-5">
                                    <input id="masa_pendidikan1" class="form-control" type="date" name="masa_pendidikan1">
                                </div>

                                <label for="masa_pendidikan2" class="col-sm-2 col-sm-offset-1">s/d</label>

                                <div class="col-sm-5">
                                    <input id="masa_pendidikan2" class="form-control" type="date" name="masa_pendidikan2">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="nilai" class="col-sm-4 col-sm-offset-1">Nilai</label>
                            <div class="col-sm-7">
                                <input id="nilai" class="form-control" type="text" name="nilai" placeholder="Nilai">
                            </div>
                        </div>

                        <div class="row col-sm-6 mt-3">
                            <div class="form-group row text-center mb-5 col-sm-6">
                                <label for="ijasah" class="col-sm-12">
                                    <img class="img-thumbnail" src="{{ asset('assets/cms/images/file_upload_placeholder.png') }}" />
                                    Upload Ijasah
                                </label>
                                <div class="image-upload">
                                    <input id="ijasah" type="file" name="ijasah">
                                </div>
                            </div>
                        </div>

                        <div class="submit_form text-center">
                            <input type="submit" class="col-sm-6 mb-1 btn btn-info btn-sm" name="selanjutnya" id="selanjutnya" value="Selanjutnya">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@stop

@section('script-bottom')
    @parent
    @include(env('ADMIN_TEMPLATE').'._component.generate_forms_script')
    <script src="{{ asset('assets/cms/js/highcharts/highcharts.js') }}"></script>

    <script type="text/javascript">

    </script>
@stop
