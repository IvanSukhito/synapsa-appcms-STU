<?php

namespace App\Http\Middleware;

use App\Codes\Models\Role;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class AdminAccessPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->session()->has('admin_role'))
        {
            $adminRole = $request->session()->get('admin_role');
            $getModuleName = Route::current()->action['as'];
            $role = Cache::remember('role'.$adminRole, env('SESSION_LIFETIME'), function () use ($adminRole) {
                return Role::where('id', '=', $adminRole)->first();
            });
            if ($role) {
                $permissionRoute = json_decode($role->permission_route, TRUE);
                if (in_array($getModuleName, $permissionRoute)) {
                    return $next($request);
                }
            }

        }

        session()->flash('message', __('general.no_permission'));
        session()->flash('message_alert', 1);

        return redirect()->route('admin');
    }
}
