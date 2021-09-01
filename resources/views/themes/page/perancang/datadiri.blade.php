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
                        <a href="#" id="datadiri" class="active col-sm-2 text-center box2">Data Diri Perancang</a>
                        <a href="#" id="pendidikan" class="col-sm-2 text-center box2">Pendidikan Perancang</a>
                        <a href="#" id="jenjang" class="col-sm-2 text-center box2">Jenjang Perancang</a>
                        <a href="#" id="prestasi" class="col-sm-2 text-center box2">Prestasi Perancang</a>
                    </div>
                    <hr />

                    <!-- Input -->
                    <form class="form-input" method="#" action="#">

                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group row text-center mb-3">
                                    <label for="foto_diri" class="col-sm-12">
                                        <img class="img-thumbnail" src="{{ asset('assets/cms/images/foto_diri_placeholder.png') }}" />
                                        Unggah Foto Diri
                                    </label>
                                    <div class="image-upload">
                                        <input id="foto_diri" type="file" name="foto_diri">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="form-group row text-center mb-3">
                                    <label for="nama" class="col-sm-4 col-sm-offset-1">Nama</label>
                                    <div class="col-sm-7">
                                        <input id="nama" class="form-control" type="text" name="nama" placeholder="Nama">
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="jenis_kelamin" class="col-sm-4 col-sm-offset-1">Jenis Kelamin</label>
                                    <div class="col-sm-7">
                                        <select id="jenis_kelamin" class="form-control" name="jenis_kelamin">
                                            <option value="Pria">Pria</option>
                                            <option value="Wanita">Wanita</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="tempat_lahir" class="col-sm-4 col-sm-offset-1">Tempat Lahir</label>
                                    <div class="col-sm-7">
                                        <input id="tempat_lahir" class="form-control" type="text" name="tempat_lahir" placeholder="Tempat Lahir">
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="tgl_lahir" class="col-sm-4 col-sm-offset-1">Tanggal Lahir</label>
                                    <div class="col-sm-7">
                                        <input id="tgl_lahir" class="form-control" type="date" name="tgl_lahir">
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="nip" class="col-sm-4 col-sm-offset-1">NIP</label>
                                    <div class="col-sm-7">
                                        <input id="nip" class="form-control" type="text" name="nip" placeholder="NIP">
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="kartu_pegawai" class="col-sm-4 col-sm-offset-1">Kartu Pegawai</label>
                                    <div class="col-sm-7">
                                        <input id="kartu_pegawai" class="form-control" type="text" name="kartu_pegawai" placeholder="Kartu Pegawai">
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="unit_kerja" class="col-sm-4 col-sm-offset-1">Unit Kerja</label>
                                    <div class="col-sm-7">
                                        <select id="unit_kerja" class="form-control" name="unit_kerja">
                                            <option value="#">#</option>
                                            <option value="#">#</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="instansi" class="col-sm-4 col-sm-offset-1">Instansi</label>
                                    <div class="col-sm-7">
                                        <select id="instansi" class="form-control" name="instansi">
                                            <option value="#">#</option>
                                            <option value="#">#</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="alamat_kantor" class="col-sm-4 col-sm-offset-1">Alamat Kantor</label>
                                    <div class="col-sm-7">
                                        <textarea id="alamat_kantor" class="form-control" name="alamat_kantor" placeholder="Alamat Kantor"></textarea>
                                    </div>
                                </div>

                                <div class="form-group row text-center mb-3">
                                    <label for="email" class="col-sm-4 col-sm-offset-1">Email</label>
                                    <div class="col-sm-7">
                                        <input id="email" class="form-control" type="email" name="email" placeholder="Email">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row col-sm-6 mt-3">
                            <div class="form-group row text-center mb-5 col-sm-6">
                                <label for="ktp" class="col-sm-12">
                                    <img class="img-thumbnail" src="{{ asset('assets/cms/images/file_upload_placeholder.png') }}" />
                                    Unggah KTP
                                </label>
                                <div class="image-upload">
                                    <input id="ktp" type="file" name="ktp">
                                </div>
                            </div>

                            <div class="form-group row text-center mb-5 col-sm-6">
                                <label for="ktp" class="col-sm-12">
                                    <img class="img-thumbnail" src="{{ asset('assets/cms/images/file_upload_placeholder.png') }}" />
                                    Unggah Karpeg
                                </label>
                                <div class="image-upload">
                                    <input id="ktp" type="file" name="ktp">
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
