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
                        <a href="#" id="pendidikan" class="col-sm-2 text-center box2">Pendidikan Perancang</a>
                        <a href="#" id="jenjang" class="active col-sm-2 text-center box2">Jenjang Perancang</a>
                        <a href="#" id="prestasi" class="col-sm-2 text-center box2">Prestasi Perancang</a>
                    </div>
                    <hr />

                    <!-- Input -->
                    <form class="form-input" method="#" action="#">

                        <div class="form-group row text-center mb-3">
                            <label for="atasan_langsung" class="col-sm-4 col-sm-offset-1">Atasan Langsung</label>
                            <div class="col-sm-7">
                                <select id="atasan_langsung" class="form-control" name="atasan_langsung">
                                    <option value="Atasan1">Atasan2</option>
                                    <option value="Atasan2">Atasan2</option>
                                </select>
                                <div class="text-left">
                                    <label>Budi Atasan</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="pangkat" class="col-sm-4 col-sm-offset-1">Pangkat</label>
                            <div class="col-sm-7">
                                <select id="pangkat" class="form-control" name="pangkat">
                                    <option value="Pangkat1">Pangkat2</option>
                                    <option value="Pangkat2">Pangkat2</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="golongan" class="col-sm-4 col-sm-offset-1">Golongan</label>
                            <div class="col-sm-7">
                                <select id="golongan" class="form-control" name="golongan">
                                    <option value="Golongan1">Golongan2</option>
                                    <option value="Golongan2">Golongan2</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="jenjang" class="col-sm-4 col-sm-offset-1">Jenjang</label>
                            <div class="col-sm-7">
                                <select id="jenjang" class="form-control" name="jenjang">
                                    <option value="Jenjang1">Jenjang2</option>
                                    <option value="Jenjang2">Jenjang2</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="tahun_pendidikan_pelatihan1" class="col-sm-4 col-sm-offset-1">Tahun Pelaksanaan Pendidikan dan Pelatihan</label>
                            <div class="col-sm-7 row">
                                <div class="col-sm-5">
                                    <input id="tahun_pendidikan_pelatihan1" class="form-control" type="date" name="tahun_pendidikan_pelatihan1">
                                </div>

                                <label for="tahun_pendidikan_pelatihan2" class="col-sm-2 col-sm-offset-1">s/d</label>

                                <div class="col-sm-5">
                                    <input id="tahun_pendidikan_pelatihan2" class="form-control" type="date" name="tahun_pendidikan_pelatihan2">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="masa_penilaian_angka_kredit" class="col-sm-4 col-sm-offset-1">Masa Penilaian Angka Kredit</label>
                            <div class="col-sm-7">
                                <input id="masa_penilaian_angka_kredit" class="form-control" type="date" name="masa_penilaian_angka_kredit">
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="tgl_penetapan_angka_kredit" class="col-sm-4 col-sm-offset-1">Tanggal Penetapan Angka Kredit</label>
                            <div class="col-sm-7">
                                <input id="tgl_penetapan_angka_kredit" class="form-control" type="date" name="tgl_penetapan_angka_kredit">
                            </div>
                        </div>

                        <div class="form-group row text-center mb-3">
                            <label for="nmr_penetapan_angka_kredit" class="col-sm-4 col-sm-offset-1">Nomor Penetapan Angka Kredit</label>
                            <div class="col-sm-7">
                                <input id="nmr_penetapan_angka_kredit" class="form-control" type="text" name="nmr_penetapan_angka_kredit" placeholder="Nomor Penetapan Angka Kredit">
                            </div>
                        </div>

                        <div class="row col-sm-6 mt-3">
                            <div class="form-group row text-center mb-5 col-sm-6">
                                <label for="upload" class="col-sm-12">
                                    <img class="img-thumbnail" src="{{ asset('assets/cms/images/file_upload_placeholder.png') }}" />
                                    Upload
                                </label>
                                <div class="image-upload">
                                    <input id="upload" type="file" name="upload">
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
