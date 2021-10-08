<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Models\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $data;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->data = [];
    }

    public function profile()
    {
        $data = $this->data;
        $admin_id = session()->get('admin_id');
        $get_data = Admin::where('id', $admin_id)->first();

        $data['data'] = $get_data;
        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.profile');
        $data['thisLabel'] = __('general.title_show', ['field' => __('general.profile') . ' ' . $get_data->name]);
        $data['thisRoute'] = 'profile';
        $data['passing'] = generatePassingData([
            'name' => [],
            'username' => []
        ]);

        return view(env('ADMIN_TEMPLATE').'.page.profile.show', $data);
    }

    public function getProfile()
    {
        $data = $this->data;
        $admin_id = session()->get('admin_id');
        $get_data = Admin::where('id', $admin_id)->first();

        $data['data'] = $get_data;
        $data['formsTitle'] = __('general.title_edit', ['field' => __('general.profile') . ' ' . $get_data->name]);
        $data['thisLabel'] = __('general.title_show', ['field' => __('general.profile') . ' ' . $get_data->name]);
        $data['thisRoute'] = 'profile';
        $data['viewType'] = 'edit';
        $data['passing'] = generatePassingData([
            'name' => [
                'validation' => [
                    'edit' => 'required'
                ]
            ],
            'username' => [
                'validation' => [
                    'edit' => 'required'
                ]
            ]
        ]);

        return view(env('ADMIN_TEMPLATE').'.page.profile.edit', $data);
    }

    public function postProfile()
    {
        $admin_id = session()->get('admin_id');

        $validator = [
            'username' => 'required|unique:admin,username,'.$admin_id,
            'name' => 'required'
        ];

        $data = $this->validate($this->request, $validator);

        session()->put('admin_name', $data['name']);

        $getDate = Admin::where('id', $admin_id)->first();
        foreach ($validator as $key => $value) {
            $getDate->$key = $this->request->get($key);
        }
        $getDate->save();

        session()->flash('message', __('general.success_update'));
        session()->flash('message_alert', 2);

        return redirect()->route('admin.profile');
    }

    public function getPassword()
    {
        $admin_id = session()->get('admin_id');
        $data = $this->data;
        $get_data = Admin::where('id', $admin_id)->first();

        $data['data'] = $get_data;
        $data['formsTitle'] = __('general.title_edit', ['field' => __('general.password') . ' ' . $get_data->name]);
        $data['thisLabel'] = __('general.title_show', ['field' => __('general.profile') . ' ' . $get_data->name]);
        $data['thisRoute'] = 'profile';
        $data['viewType'] = 'edit';
        $data['passing'] = generatePassingData([
            'old_password' => [
                'type' => 'password',
                'validation' => [
                    'edit' => 'required'
                ]
            ],
            'password' => [
                'type' => 'password',
                'validation' => [
                    'edit' => 'required|confirmed'
                ]
            ],
            'password_confirmation' => [
                'type' => 'password',
                'validation' => [
                    'edit' => 'required'
                ]
            ]
        ]);

        return view(env('ADMIN_TEMPLATE').'.page.profile.password', $data);

    }

    public function postPassword(Request $request)
    {
        $admin_id = session()->get('admin_id');

        $data = $this->validate($request, [
            'old_password' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        $account = Admin::where('id', $admin_id)->first();
        if(!$account) {
            return redirect()->route('admin.profile');
        }

        if(!app('hash')->check($data['old_password'], $account->password)) {
            return redirect()->back()->withInput()->withErrors(
                [
                    'password' => __('general.error_old_password')
                ]
            );
        }

        $getDate = Admin::where('id', $admin_id)->first();
        $getDate->password = app('hash')->make($data['password']);
        $getDate->save();

        session()->flash('message', __('general.success_update'));
        session()->flash('message_alert', 2);

        return redirect()->route('admin.profile');
    }

}
