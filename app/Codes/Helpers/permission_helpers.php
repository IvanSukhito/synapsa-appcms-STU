<?php
if ( ! function_exists('generateMenu')) {
    function generateMenu() {
        $html = '';
        $adminRole = session()->get('admin_role');
        if ($adminRole) {
            $role = \Illuminate\Support\Facades\Cache::remember('role'.$adminRole, env('SESSION_LIFETIME'), function () use ($adminRole) {
                return \App\Codes\Models\Role::where('id', '=', $adminRole)->first();
            });
            if ($role) {
                $permissionRoute = json_decode($role->permission_route, TRUE);
                $getRoute = \Illuminate\Support\Facades\Route::current()->action['as'];
                foreach (listGetPermission(listAllMenu(), $permissionRoute) as $key => $value) {
                    $active = '';
                    $class = '';
                    foreach ($value['active'] as $getActive) {
                        if (strpos($getRoute, $getActive) === 0) {
                            $active = ' active';
                        }
                    }
                    if (isset($value['inactive'])) {
                        foreach ($value['inactive'] as $getInActive) {
                            if (strpos($getRoute, $getInActive) === 0) {
                                $active = '';
                            }
                        }
                    }

                    if (in_array($value['type'], [2]) && strlen($active) > 0) {
                        $class .= ' nav-item has-treeview menu-open';
                        $extraLi = '<i class="right fa fa-angle-left"></i>';
                    }
                    else if (in_array($value['type'], [2])) {
                        $class .= ' nav-item has-treeview';
                        $extraLi = '<i class="right fa fa-angle-left"></i>';
                    }
                    else {
                        $class .= 'nav-item';
                        $extraLi = '';
                    }

                    if(isset($value['route'])) {
                        $route = route($value['route']);
                    }
                    else {
                        $route = '#';
                    }

                    $getIcon = isset($value['icon']) ? $value['icon'] : '';
                    $getAdditional = isset($value['additional']) ? $value['additional'] : '';
                    $html .= '<li class="'.$class.'">
                    <a href="'.$route.'" title="'.$value['name'].'" class="nav-link'.$active.'">
                    '.$getIcon.'
                    <p>'.
                        $value['title'].$extraLi.$getAdditional.'</p></a>';

                    if (in_array($value['type'], [2])) {
                        $html .= '<ul class="nav nav-treeview">';
                        $html .= getMenuChild($value['data'], $getRoute);
                        $html .= '</ul>';
                    }

                    $html .= '</a></li>';
                }
            }
        }
        return $html;
    }
}

if ( ! function_exists('getMenuChild')) {
    function getMenuChild($data, $getRoute) {
        $html = '';

        foreach ($data as $value) {
            $active = '';
            foreach ($value['active'] as $getActive) {
                if (strpos($getRoute, $getActive) === 0) {
                    $active = 'active';
                }
            }
            if (isset($value['inactive'])) {
                foreach ($value['inactive'] as $getInActive) {
                    if (strpos($getRoute, $getInActive) === 0) {
                        $active = '';
                    }
                }
            }

            if(isset($value['route'])) {
                $route = route($value['route']);
            }
            else {
                $route = '#';
            }

            $html .= '<li class="nav-item">
                    <a href="'.$route.'" class=" nav-link '.$active.'" title="'.$value['name'].'">
                    <i class="fa fa-circle-o nav-icon"></i><p>'.
                    $value['title'];
            $html .= '</p></a></li>';
        }

        return $html;
    }
}

if ( ! function_exists('getDetailPermission')) {
    function getDetailPermission($module, $permission = ['create' => false,'edit' => false,'show' => false,'destroy' => false]) {
        $adminRole = session()->get('admin_role');
        if ($adminRole) {
            $role = \Illuminate\Support\Facades\Cache::remember('role'.$adminRole, env('SESSION_LIFETIME'), function () use ($adminRole) {
                return \App\Codes\Models\Role::where('id', '=', $adminRole)->first();
            });
            if ($role) {
                $permissionData = json_decode($role->permission_data, TRUE);
                if( isset($permissionData[$module])) {
                    foreach ($permissionData[$module] as $key => $value) {
                        $permission[$key] = true;
                    }
                }
            }
        }
        return $permission;
    }
}

if ( ! function_exists('getValidatePermissionMenu')) {
    function getValidatePermissionMenu($permission) {
        $listMenu = [];
        if ($permission) {
            foreach ($permission as $key => $route) {
                if ($key == 'super_admin') {
                    $listMenu['super_admin'] = 1;
                }
                else {
                    if (is_array($route)) {
                        foreach ($route as $key2 => $route2) {
                            $listMenu[$key][$key2] = 1;
                        }
                    }
                }
            }
        }


        return $listMenu;
    }
}

if ( ! function_exists('generateListPermission')) {
    function generateListPermission($data = null) {
        $value = isset($data['super_admin']) ? 'checked' : '';
        $html = '<label for="super_admin">
                    <input '.$value.' style="margin-right: 5px;" type="checkbox" class="checkThis super_admin"
                    data-name="super_admin" name="permission[super_admin]" value="1" id="super_admin"/>
                    Super Admin
                </label><br/><br/>';
        $html .= createTreePermission(listAllMenu(), 0, 'checkThis super_admin', $data);
        return $html;
    }
}

if ( ! function_exists('createTreePermission')) {
    function createTreePermission($data, $left = 0, $class = '', $getData) {
        $html = '';
        foreach ($data as $index => $list) {
            if (in_array($list['type'], [2])) {
                $html .= '<label>'.$list['name'].'</label><br/>';
                $html .= createTreePermission($list['data'], $left + 1, $class, $getData);
            }
            else {
                $value = isset($getData[$list['key']]) ? 'checked' : '';
                $html .= '<label for="checkbox-'.$index.'-'.$list['key'].'">
                            <input '.$value.' style="margin-left: '.($left*30).'px; margin-right: 5px;" type="checkbox"
                            class="'.$class.' '.$list['key'].'" data-name="'.$list['key'].'" name="permission['.$list['key'].']"
                            value="1" id="checkbox-'.$index.'-'.$list['key'].'"/>
                            '.$list['name'].
                    '</label><br/>';
                $html .= getAttributePermission($list['key'], $index, $left + 1, $class.' '.$list['key'], $getData);
                $html .= '<br/>';
            }
        }
        return $html;
    }
}

if ( ! function_exists('getAttributePermission')) {
    function getAttributePermission($module, $index, $left, $class = '', $getData) {
        $html = '';
        $list = listAvailablePermission();
        if (isset($list[$module])) {
            foreach ($list[$module] as $key => $value) {
                $value = isset($getData[$module][$key]) ? 'checked' : '';
                $html .= '<label for="checkbox-'.$index.'-'.$module.'-'.$key.'">
                            <input '.$value.' style="margin-left: '.($left*30).'px; margin-right: 5px;" type="checkbox"
                            class="'.$class.'" name="permission['.$module.']['.$key.']" value="1"
                            id="checkbox-'.$index.'-'.$module.'-'.$key.'"/>
                            '.$key.
                        '</label><br/>';
            }
        }
        return $html;
    }
}

if ( ! function_exists('getPermissionRouteList')) {
    function getPermissionRouteList($listMenu) {
        $listAllowed = [];
        $listPermission = listAvailablePermission();
        foreach ($listPermission as $key => $list) {
            if ($key == 'super_admin')
                continue;
            foreach ($list as $key2 => $listRoute) {
                if (isset($listMenu[$key][$key2])) {
                    foreach ($listRoute as $value) {
                        $listAllowed[] = $value;
                    }
                }
            }
        }
        return $listAllowed;
    }
}

if ( ! function_exists('listGetPermission')) {
    function listGetPermission($listMenu, $permissionRoute)
    {
        $result = [];
        if ($permissionRoute) {
            foreach ($listMenu as $list) {
                if ($list['type'] == 1) {
                    if (in_array($list['route'], $permissionRoute)) {
                        $result[] = $list;
                    }
                }
                else {
                    $getResult = listGetPermission($list['data'], $permissionRoute);
                    if (count($getResult) > 0) {
                        $list['data'] = $getResult;
                        $result[] = $list;
                    }
                }
            }
        }

        return $result;
    }
}

if ( ! function_exists('listAllMenu')) {
    function listAllMenu()
    {
        return [
            [
                'name' => __('general.page'),
                'icon' => '<i class="nav-icon fa fa-clipboard"></i>',
                'title' => __('general.page'),
                'active' => ['admin.page.'],
                'route' => 'admin.page.index',
                'key' => 'page',
                'type' => 1,
            ],
            [
                'name' => __('general.how_to_play'),
                'icon' => '<i class="nav-icon fa fa-question-circle-o"></i>',
                'title' => __('general.how_to_play'),
                'active' => ['admin.how-to-play.'],
                'route' => 'admin.how-to-play.index',
                'key' => 'how-to-play',
                'type' => 1,
            ],
            [
                'name' => __('general.riddle'),
                'icon' => '<i class="nav-icon fa fa-gamepad"></i>',
                'additional' => '<small class="label right bg-green">new</small>',
                'title' => __('general.riddle'),
                'active' => [
                    'admin.v1-questions.',
                    'admin.v1-question-details.',
                    'admin.v1-mantra.',
                    'admin.v1-users.',
                    'admin.v1-log-login.',
                    'admin.v1-games.'
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.questions'),
                        'title' => __('general.questions'),
                        'active' => ['admin.v1-questions.', 'admin.v1-question-details'],
                        'route' => 'admin.v1-questions.index',
                        'key' => 'v1-questions',
                        'type' => 1
                    ],
                    [
                        'name' => __('general.mantra'),
                        'title' => __('general.mantra'),
                        'active' => ['admin.v1-mantra.'],
                        'route' => 'admin.v1-mantra.index',
                        'key' => 'v1-mantra',
                        'type' => 1
                    ],
                    [
                        'name' => __('general.users'),
                        'title' => __('general.users'),
                        'active' => ['admin.v1-users.'],
                        'route' => 'admin.v1-users.index',
                        'key' => 'v1-users',
                        'type' => 1
                    ],
                    [
                        'name' => __('general.log_login'),
                        'title' => __('general.log_login'),
                        'active' => ['admin.v1-log-login.'],
                        'route' => 'admin.v1-log-login.index',
                        'key' => 'v1-log-login',
                        'type' => 1
                    ],
                    [
                        'name' => __('general.games'),
                        'title' => __('general.games'),
                        'active' => ['admin.v1-games.'],
                        'route' => 'admin.v1-games.index',
                        'key' => 'v1-games',
                        'type' => 1
                    ]
                ]
            ],
            [
                'name' => __('general.setting'),
                'icon' => '<i class="nav-icon fa fa-gear"></i>',
                'title' => __('general.setting'),
                'active' => [
                    'admin.settings.',
                    'admin.admin.',
                    'admin.role.'
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.setting'),
                        'title' => __('general.setting'),
                        'active' => ['admin.settings.'],
                        'route' => 'admin.settings.index',
                        'key' => 'settings',
                        'type' => 1
                    ],
                    [
                        'name' => __('general.admin'),
                        'title' => __('general.admin'),
                        'active' => ['admin.admin.'],
                        'route' => 'admin.admin.index',
                        'key' => 'admin',
                        'type' => 1
                    ],
                    [
                        'name' => __('general.role'),
                        'title' => __('general.role'),
                        'active' => ['admin.role.'],
                        'route' => 'admin.role.index',
                        'key' => 'role',
                        'type' => 1
                    ]
                ]
            ],

        ];
    }
}

if ( ! function_exists('listAvailablePermission'))
{
    function listAvailablePermission() {

        $listPermission = [];

        foreach ([
            'settings',
            'page',
            'v1-users',
                 ] as $keyPermission) {
            $listPermission[$keyPermission] = [
                'list' => [
                    'admin.'.$keyPermission.'.index',
                    'admin.'.$keyPermission.'.dataTable'
                ],
                'edit' => [
                    'admin.'.$keyPermission.'.edit',
                    'admin.'.$keyPermission.'.update'
                ],
                'show' => [
                    'admin.'.$keyPermission.'.show'
                ]
            ];
        }

        foreach ([
                     'admin',
                     'role',
                     'privilege',
                     'how-to-play',
                     'v1-questions',
                 ] as $keyPermission) {
            $listPermission[$keyPermission] = [
                'list' => [
                    'admin.'.$keyPermission.'.index',
                    'admin.'.$keyPermission.'.dataTable'
                ],
                'create' => [
                    'admin.'.$keyPermission.'.create',
                    'admin.'.$keyPermission.'.store'
                ],
                'edit' => [
                    'admin.'.$keyPermission.'.edit',
                    'admin.'.$keyPermission.'.update'
                ],
                'show' => [
                    'admin.'.$keyPermission.'.show'
                ],
                'destroy' => [
                    'admin.'.$keyPermission.'.destroy'
                ]
            ];
        }

        foreach ([
                     'v1-mantra',
                 ] as $keyPermission) {
            $listPermission[$keyPermission] = [
                'list' => [
                    'admin.'.$keyPermission.'.index',
                    'admin.'.$keyPermission.'.dataTable'
                ],
                'create' => [
                    'admin.'.$keyPermission.'.create',
                    'admin.'.$keyPermission.'.store'
                ],
                'edit' => [
                    'admin.'.$keyPermission.'.edit',
                    'admin.'.$keyPermission.'.update'
                ],
                'show' => [
                    'admin.'.$keyPermission.'.show'
                ]
            ];
        }

        foreach ([
                     'v1-log-login',
                     'v1-games',
                 ] as $keyPermission) {
            $listPermission[$keyPermission] = [
                'list' => [
                    'admin.'.$keyPermission.'.index',
                    'admin.'.$keyPermission.'.dataTable'
                ],
                'show' => [
                    'admin.'.$keyPermission.'.show'
                ]
            ];
        }

        $listPermission['v1-questions']['list'][] = 'admin.v1-question-details.index';
        $listPermission['v1-questions']['list'][] = 'admin.v1-question-details.dataTable';
        $listPermission['v1-questions']['create'][] = 'admin.v1-question-details.create';
        $listPermission['v1-questions']['create'][] = 'admin.v1-question-details.store';
        $listPermission['v1-questions']['edit'][] = 'admin.v1-question-details.edit';
        $listPermission['v1-questions']['edit'][] = 'admin.v1-question-details.update';
        $listPermission['v1-questions']['show'][] = 'admin.v1-question-details.show';

        return $listPermission;
    }
}
