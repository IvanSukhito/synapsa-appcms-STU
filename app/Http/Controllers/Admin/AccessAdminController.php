<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\AccessLogin;
use App\Codes\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccessAdminController extends Controller
{
    protected $request;
    protected $accessLogin;
    protected $data = [];

    public function __construct(Request $request, AccessLogin $accessLogin)
    {
        $this->request = $request;
        $this->accessLogin = $accessLogin;
    }

    public function getLogin()
    {
        $data = $this->data;

        return view(env('ADMIN_TEMPLATE').'.page.login', $data);
    }

    public function postLogin()
    {
        $this->validate($this->request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = $this->accessLogin->cekLogin($this->request->get('username'),
            $this->request->get('password'), 'Admin', 'username', 'password', ['status'=>80]);
          //dd($user);
        if ($user) {
            $getRole = Role::where('id', $user->role_id)->first();
            $getPermissionData = isset($getRole) ? json_decode($getRole->permission_data, TRUE) : null;

            $getRoleClinic = isset($getPermissionData['role_clinic']) ? 1 : 0;

            //dd($getRoleClinic);
            $getClinic = 0;
            if ($getRoleClinic == 1) {
                $getClinicData = $user->getKlinik()->first();
                if ($getClinicData) {
                    $getClinic = $getClinicData->id;
                }
            }

            session()->flush();
            session()->put('admin_id', $user->id);
            session()->put('admin_name', $user->name);
            session()->put('admin_role', $user->role_id);
            session()->put('admin_clinic_id', $getClinic);
            session()->put('admin_role_clinic', $getRoleClinic);
            session()->put('admin_super_admin', isset($getPermissionData['super_admin']) ? 1 : 0);
            try {
                session_start();
                $_SESSION['set_login_ck_editor'] = 1;
            }
            catch (\Exception $e) {

            }

            return redirect()->route('admin');
        }
        else {
            return redirect()->back()->withInput()->withErrors(
                [
                    'error_login' => __('general.error_login')
                ]
            );
        }
    }

    public function doLogout()
    {
        try {
            session_start();
            unset($_SESSION['set_login_ck_editor']);
            session_destroy();
        }
        catch (\Exception $e) {

        }
        session()->flush();

        return redirect()->route('admin.login');
    }

}
