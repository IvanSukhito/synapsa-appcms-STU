<?php

namespace App\Http\Controllers\Website\V1;

use App\Codes\Logic\XenditLogic;
use App\Codes\Models\V1\ForgetPassword;
use App\Codes\Models\V1\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Xendit\EWallets;

class GeneralController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function xendit()
    {
        $xenditLogic = new XenditLogic();
//        $getData = $xenditLogic->infoEWallet('5e943709950d7621c1d57639');
        $getData = $xenditLogic->createEWallet('123456789005', 'OVO', 160000, '08211495299');
        dd($getData);
//
//        $getData = $xenditLogic->simulatePaymentVA('va-123456789002', 150000);
        $getData = $xenditLogic->getInfoVa('6148460013deaf1ca3d26de2');
        dd($getData);
    }

    public function changeTokenPassword()
    {
        $getCode = $this->request->get('code');
        $getEmail = $this->request->get('email');

        $getForgetPassword = ForgetPassword::where('email', $getEmail)->where('code', $getCode)->first();
        if ($getForgetPassword) {
            if ($getForgetPassword->status == 1) {
                return view('change_password', [
                    'code' => $getCode,
                    'email' => $getEmail
                ]);
            }
        }

        return view('error');

    }

    public function updatePassword()
    {
        $this->validate($this->request, [
            'code' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6'
        ], [], [
            'password' => 'Password Baru',
            'password_confirmation' => 'Konfirmasi Password Baru'
        ]);
        $getCode = $this->request->get('code');
        $getEmail = $this->request->get('email');
        $getPassword = $this->request->get('password');

        $getForgetPassword = ForgetPassword::where('email', $getEmail)->where('code', $getCode)->first();
        if ($getForgetPassword) {
            if ($getForgetPassword->status == 1) {
                $getUser = Users::where('email', $getEmail)->where('status', 80)->first();
                if ($getUser) {
                    $getForgetPassword->status = 80;
                    $getForgetPassword->save();

                    $getUser->password = bcrypt($getPassword);
                    $getUser->save();

                    return view('error', ['message' => 'Success Change your Password']);

                }
            }
        }

        return view('error', ['message' => 'Forgot password already used']);

    }

    public function confirmEmail()
    {
        $getCode = $this->request->get('code');
        $getEmail = $this->request->get('email');
    }

    public function confirmPhone()
    {
        $getCode = $this->request->get('code');
        $getPhone = $this->request->get('phone');
    }

}
