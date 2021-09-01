<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Models\Golongan;
use App\Codes\Models\JabatanPerancang;
use App\Codes\Models\JenjangPerancang;
use App\Codes\Models\MsKegiatan;
use App\Codes\Models\Pendidikan;
use App\Codes\Models\UnitKerja;
use App\Codes\Models\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $data;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->data = [
            'thisLabel' => 'Dashboard'
        ];
    }

    public function dashboard()
    {
        $data = $this->data;

//        DB::beginTransaction();
//        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile('./uploads/ms_kegiatan_rev.xlsx');
//        $reader->setReadDataOnly(TRUE);
//        $spreadsheet = $reader->load('./uploads/ms_kegiatan_rev.xlsx');
//        $spreadsheet = $spreadsheet->getActiveSheet();
//        $data_array =  $spreadsheet->toArray();
//        foreach ($data_array as $index => $val) {
//            if ($index > 1) {
//                $getName = $val[4];
//                $getSatuan = $val[6] ?? '';
//                if ($getSatuan == null || $getSatuan == 'NULL') {
//                    $getSatuan = '';
//                }
//                $getId = intval($val[0]);
//                MsKegiatan::where('id', $getId)->update([
//                    'name' => $getName,
//                    'satuan' => $getSatuan
//                ]);
//            }
//        }
//        DB::commit();
//        dd("A");
//
//        DB::beginTransaction();
//        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile('./uploads/pak.xlsx');
//        $reader->setReadDataOnly(TRUE);
//        $spreadsheet = $reader->load('./uploads/pak.xlsx');
//        $spreadsheet = $spreadsheet->getActiveSheet();
//        $data_array =  $spreadsheet->toArray();
//
//        $getListJabatan = JabatanPerancang::pluck('id', 'name')->toArray();
//        $getListGolongan = Golongan::pluck('id', 'name')->toArray();
//        $getListPendidikan = Pendidikan::pluck('id', 'name')->toArray();
//        $getListUnitKerja = UnitKerja::pluck('id', 'name')->toArray();
//        $getListJenjangPerancang = JenjangPerancang::pluck('id', 'name')->toArray();
//        $perancangId = 2;
//        $atasanId = 3;
//        $seketariatId = 4;
//        $timId = 5;
//
//        foreach ($data_array as $index => $val) {
//
//            if ($index <= 0) {
//                continue;
//            }
//
//            $getJabatan = isset($getListJabatan[$val[5]]) ? $getListJabatan[$val[5]] : 0;
//            $getGolongan = isset($getListGolongan[$val[7]]) ? $getListGolongan[$val[7]] : 0;
//            $getPendidikan = isset($getListPendidikan[$val[9]]) ? $getListPendidikan[$val[9]] : 0;
//            $getUnitKerja = isset($getListUnitKerja[$val[11]]) ? $getListUnitKerja[$val[11]] : 0;
//            $getJenjangPerancang = isset($getListJenjangPerancang[$val[13]]) ? $getListJenjangPerancang[$val[13]] : 0;
//
//            $setRoleId = $perancangId;
//            $setPerancang = 0;
//            $setAtasan = 0;
//            $setSek = 0;
//            $setTim = 0;
//            if (intval($val[27]) == 1) {
//                $setPerancang = 1;
//                $setRoleId = $perancangId;
//            }
//            else if (intval($val[28]) == 1) {
//                $setAtasan = 1;
//                $setRoleId = $atasanId;
//            }
//            else if (intval($val[29]) == 1) {
//                $setSek = 1;
//                $setRoleId = $seketariatId;
//            }
//            else if (intval($val[30]) == 1) {
//                $setTim = 1;
//                $setRoleId = $timId;
//            }
//
//            Users::firstOrCreate([
//                'username' => $val[1]
//            ], [
//                'upline_id' => 0,
//                'pangkat_id' => 1,
//                'golongan_id' => $getGolongan,
//                'jenjang_perancang_id' => $getJenjangPerancang,
//                'jabatan_perancang_id' => $getJabatan,
//                'pendidikan_id' => $getPendidikan,
//                'instansi_id' => 0,
//                'unit_kerja_id' => $getUnitKerja,
//                'name' => $val[0],
//                'password' => bcrypt($val[1]),
//                'email' => $val[2],
//                'tempat_lahir' => $val[17],
//                'tgl_lahir' => strtotime($val[18]) > 100 ? date('Y-m-d', strtotime($val[18])) : null,
//                'gender' => intval($val[14]),
//                'alamat' => $val[16],
//                'alamat_kantor' => $val[16],
//                'tmt_pangkat' => strtotime($val[31]) > 100 ? date('Y-m-d', strtotime($val[31])) : null,
//                'tmt_jabatan' => strtotime($val[21]) > 100 ? date('Y-m-d', strtotime($val[21])) : null,
//                'masa_penilaian_terakhir_awal' => strtotime($val[35]) > 100 ? date('Y-m-d', strtotime($val[35])) : null,
//                'masa_penilaian_terakhir_akhir' => strtotime($val[36]) > 100 ? date('Y-m-d', strtotime($val[36])) : null,
//                'angka_kredit_terakhir' => intval($val[23]),
//                'role_id' => $setRoleId,
//                'perancang' => $setPerancang,
//                'atasan' => $setAtasan,
//                'sekretariat' => $setSek,
//                'tim_penilai' => $setTim,
//                'progress' => 1,
//                'status' => 1,
//            ]);
//        }
//        DB::commit();
//        dd("A");

        return view(env('ADMIN_TEMPLATE').'.page.dashboard', $data);
    }

}
