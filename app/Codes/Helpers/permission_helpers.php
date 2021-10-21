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
                if ($key == 'check_all') {
                    $listMenu['check_all'] = 1;
                }
                else if ($key == 'super_admin') {
                    $listMenu['super_admin'] = 1;
                }
                else if ($key == 'role_clinic') {
                    $listMenu['role_clinic'] = 1;
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
        $html = '';

        $value = isset($data['check_all']) ? 'checked' : '';
        $html .= '<label for="check_all">
                    <input '.$value.' style="margin-right: 5px;" type="checkbox" class="checkThis check_all"
                    data-name="check_all" name="permission[check_all]" value="1" id="check_all"/>
                    ALL
                </label><br/><br/>';
        $value = isset($data['super_admin']) ? 'checked' : '';
        $html .= '<label for="super_admin">
                    <input '.$value.' style="margin-right: 5px;" type="checkbox" class="super_admin"
                    data-name="super_admin" name="permission[super_admin]" value="1" id="super_admin"/>
                    Super Admin
                </label><br/><br/>';
        $value = isset($data['role_clinic']) ? 'checked' : '';
        $html .= '<label for="role_clinic">
                    <input '.$value.' style="margin-right: 5px;" type="checkbox" class="role_clinic"
                    data-name="role_clinic" name="permission[role_clinic]" value="1" id="role_clinic"/>
                    Clinic
                </label><br/><br/>';
        $html .= createTreePermission(listAllMenu(), 0, 'checkThis check_all', $data);
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
            if ($key == 'role_clinic')
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
                'name' => __('general.clinic_info'),
                'icon' => '<i class="nav-icon fa fa-hospital-o"></i>',
                'title' => __('general.clinic_info'),
                'active' => ['admin.clinic_info.'],
                'route' => 'admin.clinic_info.index',
                'key' => 'clinic_info',
                'type' => 1,
            ],
            [
                'name' => __('general.doctor_clinic'),
                'icon' => '<i class="nav-icon fa fa-user-plus"></i>',
                'title' => __('general.doctor_clinic'),
                'active' => ['admin.doctor_clinic.'],
                'route' => 'admin.doctor_clinic.index',
                'key' => 'doctor_clinic',
                'type' => 1,
            ],
            [
                'name' => __('general.user_clinic'),
                'icon' => '<i class="nav-icon fa fa-user"></i>',
                'title' => __('general.user_clinic'),
                'active' => ['admin.user_clinic.'],
                'route' => 'admin.user_clinic.index',
                'key' => 'user_clinic',
                'type' => 1,
            ],
            [
                'name' => __('general.doctor'),
                'icon' => '<i class="nav-icon fa fa-user-md"></i>',
                'title' => __('general.doctor'),
                'active' => [
                    'admin.doctor.',
                    'admin.doctor-category.'
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.doctor'),
                        'title' => __('general.doctor'),
                        'active' => ['admin.doctor.'],
                        'route' => 'admin.doctor.index',
                        'key' => 'doctor',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.doctor_category'),
                        'title' => __('general.doctor_category'),
                        'active' => ['admin.doctor-category.'],
                        'route' => 'admin.doctor-category.index',
                        'key' => 'doctor-category',
                        'type' => 1,
                    ],

                ],
            ],
            [
                'name' => __('general.service'),
                'icon' => '<i class="nav-icon fa fa-wrench"></i>',
                'title' => __('general.service'),
                'active' => [
                    'admin.service.',
                    'admin.service-doctor.',
                    'admin.service-lab.'
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.service'),
                        'title' => __('general.service'),
                        'active' => ['admin.service.'],
                        'route' => 'admin.service.index',
                        'key' => 'service',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.product'),
                'icon' => '<i class="nav-icon fa fa-product-hunt"></i>',
                'title' => __('general.product'),
                'active' => [
                    'admin.product.',
                    'admin.product-category.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.product'),
                        'title' => __('general.product'),
                        'active' => ['admin.product.'],
                        'route' => 'admin.product.index',
                        'key' => 'product',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.product_clinic'),
                        'title' => __('general.product_clinic'),
                        'active' => ['admin.product-clinic.'],
                        'route' => 'admin.product-clinic.index',
                        'key' => 'product-clinic',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.product-category'),
                        'title' => __('general.product-category'),
                        'active' => ['admin.product-category.'],
                        'route' => 'admin.product-category.index',
                        'key' => 'product-category',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.lab'),
                'icon' => '<i class="nav-icon fa fa-flask"></i>',
                'title' => __('general.lab'),
                'active' => [
                    'admin.lab.',
                    'admin.lab-schedule.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.lab'),
                        'title' => __('general.lab'),
                        'active' => ['admin.lab.'],
                        'route' => 'admin.lab.index',
                        'key' => 'lab',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.lab_schedule'),
                        'title' => __('general.lab_schedule'),
                        'active' => ['admin.lab-schedule.'],
                        'route' => 'admin.lab-schedule.index',
                        'key' => 'lab-schedule',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.transaction'),
                'icon' => '<i class="nav-icon fa fa-shopping-cart"></i>',
                'title' => __('general.transaction'),
                'active' => [
                    'admin.transaction-lab.',
                    'admin.transaction-doctor.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.transaction_lab'),
                        'title' => __('general.transaction_lab'),
                        'active' => ['admin.transaction-lab.'],
                        'route' => 'admin.transaction-lab.index',
                        'key' => 'transaction-lab',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.transaction_doctor'),
                        'title' => __('general.transaction_doctor'),
                        'active' => ['admin.transaction-doctor.'],
                        'route' => 'admin.transaction-doctor.index',
                        'key' => 'transaction-doctor',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.transaction'),
                'icon' => '<i class="nav-icon fa fa-handshake-o"></i>',
                'title' => __('general.transaction'),
                'active' => ['admin.transaction.'],
                'route' => 'admin.transaction.index',
                'key' => 'transaction',
                'type' => 1,
            ],
            [
                'name' => __('general.appointment_lab'),
                'icon' => '<i class="nav-icon fa fa-book"></i>',
                'title' => __('general.appointment_lab'),
                'active' => ['admin.appointment-lab.'],
                'route' => 'admin.appointment-lab.index',
                'key' => 'appointment-lab',
                'type' => 1,
            ],
            [
                'name' => __('general.appointment_nurse'),
                'icon' => '<i class="nav-icon fa fa-book"></i>',
                'title' => __('general.appointment_nurse'),
                'active' => ['admin.appointment-nurse.'],
                'route' => 'admin.appointment-nurse.index',
                'key' => 'appointment-nurse',
                'type' => 1,
            ],
            [
                'name' => __('general.payment'),
                'icon' => '<i class="nav-icon fa fa-credit-card"></i>',
                'title' => __('general.payment'),
                'active' => ['admin.payment.'],
                'route' => 'admin.payment.index',
                'key' => 'payment',
                'type' => 1,
            ],
            [
                'name' => __('general.shipping'),
                'icon' => '<i class="nav-icon fa fa-shopping-cart"></i>',
                'title' => __('general.shipping'),
                'active' => ['admin.shipping.'],
                'route' => 'admin.shipping.index',
                'key' => 'shipping',
                'type' => 1,
            ],
            [
                'name' => __('general.users'),
                'icon' => '<i class="nav-icon fa fa-user"></i>',
                'title' => __('general.users'),
                'active' => [
                    'admin.users.',
                    'admin.users-doctor.',
                    'admin.users-patient.'
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.users_doctor'),
                        'title' => __('general.users_doctor'),
                        'active' => ['admin.users-doctor.'],
                        'route' => 'admin.users-doctor.index',
                        'key' => 'users-doctor',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.users_patient'),
                        'title' => __('general.users_patient'),
                        'active' => ['admin.users-patient.'],
                        'route' => 'admin.users-patient.index',
                        'key' => 'users-patient',
                        'type' => 1,
                    ],
                ],
            ],

            [
                'name' => __('general.article'),
                'icon' => '<i class="nav-icon fa fa-newspaper-o"></i>',
                'title' => __('general.article'),
                'active' => [
                    'admin.article.',
                    'admin.article-category.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.article'),
                        'title' => __('general.article'),
                        'active' => ['admin.article.'],
                        'route' => 'admin.article.index',
                        'key' => 'article',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.article-category'),
                        'title' => __('general.article-category'),
                        'active' => ['admin.article-category.'],
                        'route' => 'admin.article-category.index',
                        'key' => 'article-category',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.setting'),
                'icon' => '<i class="nav-icon fa fa-gear"></i>',
                'title' => __('general.setting'),
                'active' => [
                    'admin.settings.',
                    'admin.admin.',
                    'admin.role.',
                    'admin.faqs.',
                    'admin.klinik.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.faqs'),
                        'title' => __('general.faqs'),
                        'active' => ['admin.faqs.'],
                        'route' => 'admin.faqs.index',
                        'key' => 'faqs',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.klinik'),
                        'title' => __('general.klinik'),
                        'active' => ['admin.klinik.'],
                        'route' => 'admin.klinik.index',
                        'key' => 'klinik',
                        'type' => 1,
                    ],
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
                    ],
                ],
            ]
        ];
    }
}

if ( ! function_exists('listAvailablePermission'))
{
    function listAvailablePermission() {

        $listPermission = [];

        foreach ([
                     'settings',
                     'faqs',
                     'transaction',
                     'appointment-lab',
                     'appointment-nurse',
                     'clinic_info',
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
                     'transaction-lab',
                     'transaction-doctor',

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

        foreach ([
                     'admin',
                     'users-doctor',
                     'users-patient',
                     'role',
                     'article',
                     'product',
                     'product-clinic',
                     'lab',
                     'users',
                     'doctor',
                     'product-category',
                     'article-category',
                     'service',
                     'klinik',
                     'lab-schedule',
                     'doctor-category',
                     'payment',
                     'shipping',
                     'doctor_clinic',
                     'user_clinic',

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


        $listPermission['doctor']['create'][] = 'admin.doctor.schedule';
        $listPermission['doctor']['create'][] = 'admin.doctor.storeSchedule';
        $listPermission['doctor']['edit'][] = 'admin.doctor.updateSchedule';
        $listPermission['doctor']['destroy'][] = 'admin.doctor.destroySchedule';

        $listPermission['doctor_clinic']['create'][] = 'admin.doctor_clinic.schedule';
        $listPermission['doctor_clinic']['create'][] = 'admin.doctor_clinic.storeSchedule';
        $listPermission['doctor_clinic']['edit'][] = 'admin.doctor_clinic.updateSchedule';
        $listPermission['doctor_clinic']['destroy'][] = 'admin.doctor_clinic.destroySchedule';

        $listPermission['lab-schedule']['edit'][] = 'admin.lab-schedule.updateLab';

        $listPermission['appointment-lab']['edit'][] = 'admin.appointment-lab.approve';
        $listPermission['appointment-lab']['edit'][] = 'admin.appointment-lab.reject';

        $listPermission['appointment-nurse']['edit'][] = 'admin.appointment-nurse.approve';
        $listPermission['appointment-nurse']['edit'][] = 'admin.appointment-nurse.reject';
//
//        $listPermission['transaction']['edit'][] = 'admin.transaction.approve';
//        $listPermission['transaction']['edit'][] = 'admin.transaction.reject';

        $listPermission['transaction-lab']['edit'][] = 'admin.transaction-lab.approve';
        $listPermission['transaction-lab']['edit'][] = 'admin.transaction-lab.reject';

        $listPermission['transaction-doctor']['edit'][] = 'admin.transaction-doctor.approve';
        $listPermission['transaction-doctor']['edit'][] = 'admin.transaction-doctor.reject';

        $listPermission['product-clinic']['create'][] = 'admin.product-clinic.create2';
        $listPermission['product-clinic']['create'][] = 'admin.product-clinic.store2';

        return $listPermission;
    }
}
