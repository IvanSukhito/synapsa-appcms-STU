<?php
if ( ! function_exists('listAllMenu')) {
    /**
     * @return array[]
     */
    function listAllMenu(): array
    {
        return [
            [
                'name' => __('general.untuk_klinik'),
                'icon' => '',
                'title' => __('general.untuk_klinik'),
                'active' => [],
                'type' => 3,
                'data' => [],
            ],
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
                'name' => __('general.article_clinic'),
                'icon' => '<i class="nav-icon fa fa-newspaper-o"></i>',
                'title' => __('general.article_clinic'),
                'active' => [
                    'admin.article-clinic.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.article_clinic'),
                        'title' => __('general.article_clinic'),
                        'active' => ['admin.article-clinic.'],
                        'route' => 'admin.article-clinic.index',
                        'key' => 'article-clinic',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.doctor_clinic'),
                'icon' => '<i class="nav-icon fa fa-user-md"></i>',
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
                'active' => ['admin.user-clinic.'],
                'route' => 'admin.user-clinic.index',
                'key' => 'user-clinic',
                'type' => 1,
            ],
            [
                'name' => __('general.product'),
                'icon' => '<i class="nav-icon fa fa-tags"></i>',
                'title' => __('general.product'),
                'active' => [
                    'admin.product-clinic.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.product_clinic'),
                        'title' => __('general.product_clinic'),
                        'active' => ['admin.product-clinic.'],
                        'route' => 'admin.product-clinic.index',
                        'key' => 'product-clinic',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.lab_clinic'),
                'icon' => '<i class="nav-icon fa fa-flask"></i>',
                'title' => __('general.lab_clinic'),
                'active' => [
                    'admin.lab-clinic-schedule.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.lab_clinic_schedule'),
                        'title' => __('general.lab_clinic_schedule'),
                        'active' => ['admin.lab-clinic-schedule.'],
                        'route' => 'admin.lab-clinic-schedule.index',
                        'key' => 'lab-clinic-schedule',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.banner_clinic'),
                'icon' => '<i class="nav-icon fa fa-image"></i>',
                'title' => __('general.banner_clinic'),
                'active' => ['admin.banner-clinic.'],
                'route' => 'admin.banner-clinic.index',
                'key' => 'banner-clinic',
                'type' => 1,
            ],
            [
                'name' => __('general.pharmacy'),
                'icon' => '<i class="nav-icon fa fa-plus-square"></i>',
                'title' => __('general.pharmacy'),
                'active' => ['admin.pharmacy.'],
                'route' => 'admin.pharmacy.index',
                'key' => 'pharmacy',
                'type' => 1,
            ],
            [
                'name' => __('general.transaction'),
                'icon' => '<i class="nav-icon fa fa-shopping-cart"></i>',
                'title' => __('general.transaction'),
                'active' => [
                    'admin.transaction-lab.',
                    'admin.transaction-doctor.',
                    'admin.transaction-product.',
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
                    [
                        'name' => __('general.transaction_product'),
                        'title' => __('general.transaction_product'),
                        'active' => ['admin.transaction-product.'],
                        'route' => 'admin.transaction-product.index',
                        'key' => 'transaction-product',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.appointment_doctor_clinic'),
                'icon' => '<i class="nav-icon fa fa-book"></i>',
                'title' => __('general.appointment_doctor_clinic'),
                'active' => [
                    'admin.appointment-doctor-clinic.',
                    'admin.doctor-clinic-homecare.',
                    'admin.doctor-clinic-visit.',
                    'admin.doctor-clinic-telemed.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.homecare'),
                        'title' => __('general.homecare'),
                        'active' => ['admin.doctor-clinic-homecare.'],
                        'route' => 'admin.doctor-clinic-homecare.index',
                        'key' => 'doctor-clinic-homecare',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.visit'),
                        'title' => __('general.visit'),
                        'active' => ['admin.doctor-clinic-visit.'],
                        'route' => 'admin.doctor-clinic-visit.index',
                        'key' => 'doctor-clinic-visit',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.telemed'),
                        'title' => __('general.telemed'),
                        'active' => ['admin.doctor-clinic-telemed.'],
                        'route' => 'admin.doctor-clinic-telemed.index',
                        'key' => 'doctor-clinic-telemed',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.appointment_lab_clinic'),
                'icon' => '<i class="nav-icon fa fa-book"></i>',
                'title' => __('general.appointment_lab_clinic'),
                'active' => [
                    'admin.lab-appointment',
                    'admin.appointment-lab-clinic.',
                    'admin.appointment-lab-homecare-clinic.',
                    'admin.appointment-lab-visit-clinic.',
                    'admin.appointment-lab-schedule.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.appointment_lab_clinic'),
                        'title' => __('general.appointment_lab_clinic'),
                        'active' => ['admin.lab-appointment.'],
                        'route' => 'admin.lab-appointment.index',
                        'key' => 'lab-appointment',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.appointment_lab_homecare_clinic'),
                        'title' => __('general.appointment_lab_homecare_clinic'),
                        'active' => ['admin.appointment-lab-homecare-clinic.'],
                        'route' => 'admin.appointment-lab-homecare-clinic.index',
                        'key' => 'appointment-lab-homecare-clinic',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.appointment_lab_visit_clinic'),
                        'title' => __('general.appointment_lab_visit_clinic'),
                        'active' => ['admin.appointment-lab-visit-clinic.'],
                        'route' => 'admin.appointment-lab-visit-clinic.index',
                        'key' => 'appointment-lab-visit-clinic',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.appointment_lab_schedule'),
                        'icon' => '<i class="nav-icon fa fa-calendar-o"></i>',
                        'title' => __('general.appointment_lab_schedule'),
                        'active' => ['admin.appointment-lab-schedule.'],
                        'route' => 'admin.appointment-lab-schedule.index',
                        'key' => 'appointment-lab-schedule',
                        'type' => 1,
                    ],
                ],
            ],



            [
                'name' => __('general.untuk_admin'),
                'icon' => '',
                'title' => __('general.untuk_admin'),
                'active' => [],
                'type' => 3,
                'data' => [],
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
                'name' => __('general.lab'),
                'icon' => '<i class="nav-icon fa fa-flask"></i>',
                'title' => __('general.lab'),
                'active' => [
                    'admin.lab',
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
                'name' => __('general.banner'),
                'icon' => '<i class="nav-icon fa fa-image"></i>',
                'title' => __('general.banner'),
                'active' => ['admin.banner.'],
                'route' => 'admin.banner.index',
                'key' => 'banner',
                'type' => 1,
            ],
            [
                'name' => __('general.service'),
                'icon' => '<i class="nav-icon fa fa-medkit"></i>',
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
                'icon' => '<i class="nav-icon fa fa-tags"></i>',
                'title' => __('general.product'),
                'active' => [
                    'admin.product.',
                    'admin.product-clinic.',
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
                'name' => __('general.transaction_admin'),
                'icon' => '<i class="nav-icon fa fa-shopping-cart"></i>',
                'title' => __('general.transaction_admin'),
                'active' => [
                    'admin.transaction-lab-admin.',
                    'admin.transaction-doctor-admin.',
                    'admin.transaction-product-admin.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.transaction_lab_admin'),
                        'title' => __('general.transaction_lab_admin'),
                        'active' => ['admin.transaction-lab-admin.'],
                        'route' => 'admin.transaction-lab-admin.index',
                        'key' => 'transaction-lab-admin',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.transaction_doctor_admin'),
                        'title' => __('general.transaction_doctor_admin'),
                        'active' => ['admin.transaction-doctor-admin.'],
                        'route' => 'admin.transaction-doctor-admin.index',
                        'key' => 'transaction-doctor-admin',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.transaction_product_admin'),
                        'title' => __('general.transaction_product_admin'),
                        'active' => ['admin.transaction-product-admin.'],
                        'route' => 'admin.transaction-product-admin.index',
                        'key' => 'transaction-product-admin',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.invoice'),
                'icon' => '<i class="nav-icon fa fa-money"></i>',
                'title' => __('general.invoice'),
                'active' => ['admin.invoice.'],
                'route' => 'admin.invoice.index',
                'key' => 'invoice',
                'type' => 1,
            ],
            [
                'name' => __('general.appointment_lab'),
                'icon' => '<i class="nav-icon fa fa-book"></i>',
                'title' => __('general.appointment_lab'),
                'active' => [
                    'admin.appointment-lab.',
                    'admin.appointment-lab-homecare.',
                    'admin.appointment-lab-visit.'
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.appointment_lab_homecare'),
                        'title' => __('general.appointment_lab_homecare'),
                        'active' => ['admin.appointment-lab-homecare.'],
                        'route' => 'admin.appointment-lab-homecare.index',
                        'key' => 'appointment-lab-homecare',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.appointment_lab_visit'),
                        'title' => __('general.appointment_lab_visit'),
                        'active' => ['admin.appointment-lab-visit.'],
                        'route' => 'admin.appointment-lab-visit.index',
                        'key' => 'appointment-lab-visit',
                        'type' => 1,
                    ],
                ],
            ],
            [
                'name' => __('general.appointment_doctor'),
                'icon' => '<i class="nav-icon fa fa-book"></i>',
                'title' => __('general.appointment_doctor'),
                'active' => [
                    'admin.appointment-doctor.',
                    'admin.doctor-homecare.',
                    'admin.doctor-visit.',
                    'admin.doctor-telemed.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.homecare'),
                        'title' => __('general.homecare'),
                        'active' => ['admin.doctor-homecare.'],
                        'route' => 'admin.doctor-homecare.index',
                        'key' => 'doctor-homecare',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.visit'),
                        'title' => __('general.visit'),
                        'active' => ['admin.doctor-visit.'],
                        'route' => 'admin.doctor-visit.index',
                        'key' => 'doctor-visit',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.telemed'),
                        'title' => __('general.telemed'),
                        'active' => ['admin.doctor-telemed.'],
                        'route' => 'admin.doctor-telemed.index',
                        'key' => 'doctor-telemed',
                        'type' => 1,
                    ],
                ],
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
                'name' => __('general.users'),
                'icon' => '<i class="nav-icon fa fa-user"></i>',
                'title' => __('general.users'),
                'active' => [
                    'admin.users.',
                    'admin.users-patient.'
                ],
                'type' => 2,
                'data' => [
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
                    'admin.payment.',
                    'admin.shipping.',
                    'admin.faqs.',
                    'admin.klinik.',
                    'admin.customer-support.',
                    'admin.medicine-type.',
                    'admin.settings.',
                    'admin.admin.',
                    'admin.notification.',
                    'admin.page.',
                    'admin.role.',
                ],
                'type' => 2,
                'data' => [
                    [
                        'name' => __('general.payment'),
                        'title' => __('general.payment'),
                        'active' => ['admin.payment.'],
                        'route' => 'admin.payment.index',
                        'key' => 'payment',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.notification'),
                        'title' => __('general.notification'),
                        'active' => ['admin.notification.'],
                        'route' => 'admin.notification.index',
                        'key' => 'notification',
                        'type' => 1,
                    ],
                    [
                        'name' => __('general.shipping'),
                        'title' => __('general.shipping'),
                        'active' => ['admin.shipping.'],
                        'route' => 'admin.shipping.index',
                        'key' => 'shipping',
                        'type' => 1,
                    ],
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
                        'name' => __('general.medicine_type'),
                        'title' => __('general.medicine_type'),
                        'active' => ['admin.medicine-type.'],
                        'route' => 'admin.medicine-type.index',
                        'key' => 'medicine-type',
                        'type' => 1
                    ],
                    [
                        'name' => __('general.customer_support'),
                        'title' => __('general.customer_support'),
                        'active' => ['admin.customer-support.'],
                        'route' => 'admin.customer-support.index',
                        'key' => 'customer-support',
                        'type' => 1
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
                        'name' => __('general.page'),
                        'title' => __('general.page'),
                        'active' => ['admin.page.'],
                        'route' => 'admin.page.index',
                        'key' => 'page',
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
    /**
     * @return array
     */
    function listAvailablePermission(): array
    {

        $listPermission = [];

        foreach ([
                     'settings',
                     'page',
                     'faqs',
                     'transaction',
                     'appointment-nurse',
                     'clinic_info',
                     'transaction-product',
                     'transaction-lab',
                     'transaction-doctor',
                     'transaction-lab-admin',
                     'transaction-product-admin',
                     'transaction-doctor-admin',
                     'invoice',
                     'user-clinic',
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

                     'appointment-lab',
                     'appointment-lab-visit',
                     'appointment-lab-homecare',
                     'appointment-lab-clinic',
                     'doctor-clinic-visit',
                     'doctor-clinic-telemed',
                     'doctor-clinic-homecare',
                     'doctor-visit',
                     'doctor-telemed',
                     'doctor-homecare',
                     'pharmacy',
                     'appointment-lab-visit-clinic',
                     'appointment-lab-homecare-clinic',
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
                     'notification',
                     'users-patient',
                     'role',
                     'article',
                     'article-clinic',
                     'medicine-type',
                     'banner-category',
                     'product',
                     'product-clinic',
                     'lab',
                     'users',
                     'product-category',
                     'article-category',
                     'service',
                     'klinik',
                     'lab-clinic-schedule',
                     'lab-schedule',
                     'doctor-category',
                     'payment',
                     'shipping',
                     'doctor_clinic',
                     'doctor',
                     'customer-support',
                     'banner',
                     'banner-clinic',
                     'appointment-lab-schedule',

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
                     'lab-appointment',
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
                'destroy' => [
                    'admin.'.$keyPermission.'.destroy'
                ]
            ];
        }


        $listPermission['doctor_clinic']['create'][] = 'admin.doctor_clinic.schedule';
        $listPermission['doctor_clinic']['create'][] = 'admin.doctor_clinic.storeSchedule';
        $listPermission['doctor_clinic']['create'][] = 'admin.doctor_clinic.createschedule2';
        $listPermission['doctor_clinic']['create'][] = 'admin.doctor_clinic.storeschedule2';
        $listPermission['doctor_clinic']['edit'][] = 'admin.doctor_clinic.updateSchedule';
        $listPermission['doctor_clinic']['destroy'][] = 'admin.doctor_clinic.destroySchedule';

        $listPermission['doctor']['create'][] = 'admin.doctor.create2';
        $listPermission['doctor']['create'][] = 'admin.doctor.store2';
        $listPermission['doctor']['create'][] = 'admin.doctor.schedule';
        $listPermission['doctor']['create'][] = 'admin.doctor.storeSchedule';
        $listPermission['doctor']['create'][] = 'admin.doctor.createschedule2';
        $listPermission['doctor']['create'][] = 'admin.doctor.storeschedule2';
        $listPermission['doctor']['edit'][] = 'admin.doctor.updateSchedule';
        $listPermission['doctor']['edit'][] = 'admin.doctor.forgotPassword';
        $listPermission['doctor']['edit'][] = 'admin.doctor.updatePassword';
        $listPermission['doctor']['destroy'][] = 'admin.doctor.destroySchedule';

        $listPermission['lab-clinic-schedule']['edit'][] = 'admin.lab-clinic-schedule.updateLab';
        $listPermission['lab-clinic-schedule']['create'][] = 'admin.lab-clinic-schedule.create2';
        $listPermission['lab-clinic-schedule']['create'][] = 'admin.lab-clinic-schedule.store2';

        $listPermission['lab-schedule']['edit'][] = 'admin.lab-schedule.updateLab';

        $listPermission['appointment-lab-homecare-clinic']['list'][] = 'admin.appointment-lab-homecare-clinic.approve';
        $listPermission['appointment-lab-homecare-clinic']['list'][] = 'admin.appointment-lab-homecare-clinic.reject';
        $listPermission['appointment-lab-homecare-clinic']['list'][] = 'admin.appointment-lab-homecare-clinic.uploadHasilLab';
        $listPermission['appointment-lab-homecare-clinic']['list'][] = 'admin.appointment-lab-homecare-clinic.storeHasilLab';

        $listPermission['appointment-lab-visit-clinic']['list'][] = 'admin.appointment-lab-visit-clinic.approve';
        $listPermission['appointment-lab-visit-clinic']['list'][] = 'admin.appointment-lab-visit-clinic.reject';
        $listPermission['appointment-lab-visit-clinic']['list'][] = 'admin.appointment-lab-visit-clinic.uploadHasilLab';
        $listPermission['appointment-lab-visit-clinic']['list'][] = 'admin.appointment-lab-visit-clinic.storeHasilLab';

        $listPermission['appointment-nurse']['edit'][] = 'admin.appointment-nurse.approve';
        $listPermission['appointment-nurse']['edit'][] = 'admin.appointment-nurse.reject';

//        $listPermission['transaction']['edit'][] = 'admin.transaction.approve';
//        $listPermission['transaction']['edit'][] = 'admin.transaction.reject';

        $listPermission['transaction-lab']['edit'][] = 'admin.transaction-lab.approve';
        $listPermission['transaction-lab']['edit'][] = 'admin.transaction-lab.reject';

        $listPermission['transaction-product']['edit'][] = 'admin.transaction-product.approve';
        $listPermission['transaction-product']['edit'][] = 'admin.transaction-product.reject';

        $listPermission['transaction-doctor']['edit'][] = 'admin.transaction-doctor.approve';
        $listPermission['transaction-doctor']['edit'][] = 'admin.transaction-doctor.reject';

        $listPermission['transaction-lab-admin']['edit'][] = 'admin.transaction-lab-admin.approve';
        $listPermission['transaction-lab-admin']['edit'][] = 'admin.transaction-lab-admin.reject';

        $listPermission['transaction-product-admin']['edit'][] = 'admin.transaction-product-admin.approve';
        $listPermission['transaction-product-admin']['edit'][] = 'admin.transaction-product-admin.reject';

        $listPermission['transaction-doctor-admin']['edit'][] = 'admin.transaction-doctor-admin.approve';
        $listPermission['transaction-doctor-admin']['edit'][] = 'admin.transaction-doctor-admin.reject';

        $listPermission['product']['create'][] = 'admin.product.create2';
        $listPermission['product']['create'][] = 'admin.product.store2';

        $listPermission['klinik']['create'][] = 'admin.klinik.create2';
        $listPermission['klinik']['create'][] = 'admin.klinik.store2';

        $listPermission['users-patient']['edit'][] = 'admin.users-patient.forgotPassword';
        $listPermission['users-patient']['edit'][] = 'admin.users-patient.updatePassword';

        $listPermission['admin']['edit'][] = 'admin.admin.forgotPassword';
        $listPermission['admin']['edit'][] = 'admin.admin.updatePassword';

        $listPermission['product-clinic']['create'][] = 'admin.product-clinic.create2';
        $listPermission['product-clinic']['create'][] = 'admin.product-clinic.store2';
        $listPermission['product-clinic']['create'][] = 'admin.product-clinic.getProductSynapsa';
        $listPermission['product-clinic']['create'][] = 'admin.product-clinic.create3';
        $listPermission['product-clinic']['create'][] = 'admin.product-clinic.create4';
        $listPermission['product-clinic']['create'][] = 'admin.product-clinic.store3';

        $listPermission['doctor_clinic']['create'][] = 'admin.doctor_clinic.create2';
        $listPermission['doctor_clinic']['create'][] = 'admin.doctor_clinic.store2';
        $listPermission['doctor_clinic']['edit'][] = 'admin.doctor_clinic.forgotPassword';
        $listPermission['doctor_clinic']['edit'][] = 'admin.doctor_clinic.updatePassword';

        return $listPermission;
    }
}

if ( ! function_exists('generateMenu')) {
    /**
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function generateMenu(): string
    {
        $html = '';
        $adminRole = session()->get('admin_role');
        if ($adminRole) {
            $role = \Illuminate\Support\Facades\Cache::remember('role'.$adminRole, env('SESSION_LIFETIME'), function () use ($adminRole) {
                return \App\Codes\Models\Role::where('id', '=', $adminRole)->first();
            });
            if ($role) {
                $permissionRoute = json_decode($role->permission_route, TRUE);
                $getRoute = \Illuminate\Support\Facades\Route::current()->action['as'];

                $getMenu = listGetPermission(listAllMenu(), $permissionRoute);
                $printMenu = [];
                $prevType = 0;
                foreach ($getMenu as $index => $list) {
                    if ($prevType == $list['type'] && $prevType == 3) {
                        if (count($printMenu) > 0) {
                            unset($printMenu[$index-1]);
                        }
                    }

                    $printMenu[] = $list;
                    $prevType = $list['type'];

                }

                foreach ($printMenu as $value) {
                    if ($value['type'] == 3) {
                        $html .= '<li class="nav-header">'.$value['title'].'</li>';
                    }
                    else {
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

                        if ($value['type'] == 2 && strlen($active) > 0) {
                            $class .= ' nav-item has-treeview menu-open';
                            $extraLi = '<i class="right fa fa-angle-left"></i>';
                        }
                        else if ($value['type'] == 2) {
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

                        $getIcon = $value['icon'] ?? '';
                        $getAdditional = $value['additional'] ?? '';
                        $html .= '<li class="'.$class.'">
                            <a href="'.$route.'" title="'.$value['name'].'" class="nav-link'.$active.'">
                            '.$getIcon.'
                            <p>'.
                            $value['title'].$extraLi.$getAdditional.'</p></a>';

                        if ($value['type'] == 2) {
                            $html .= '<ul class="nav nav-treeview">';
                            $html .= getMenuChild($value['data'], $getRoute);
                            $html .= '</ul>';
                        }

                        $html .= '</a></li>';
                    }
                }
            }
        }
        return $html;
    }
}

if ( ! function_exists('getMenuChild')) {
    /**
     * @param $data
     * @param $getRoute
     * @return string
     */
    function getMenuChild($data, $getRoute): string
    {
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
    /**
     * @param $permission
     * @return array
     */
    function getValidatePermissionMenu($permission): array
    {
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
    /**
     * @param array|null $data
     * @return string
     */
    function generateListPermission(?array $data = array()): string
    {
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
    /**
     * @param $data
     * @param int $left
     * @param string $class
     * @param array|null $getData
     * @return string
     */
    function createTreePermission($data, int $left = 0, string $class = '', ?array $getData = array()): string
    {
        $html = '';
        foreach ($data as $index => $list) {
            if ($list['type'] == 2) {
                $html .= '<label>'.$list['name'].'</label><br/>';
                $html .= createTreePermission($list['data'], $left + 1, $class, $getData);
            }
            else if ($list['type'] == 3) {
                $html .= '<hr/><label>'.$list['name'].'</label><hr/>';
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
    /**
     * @param $module
     * @param $index
     * @param $left
     * @param string $class
     * @param array|null $getData
     * @return string
     */
    function getAttributePermission($module, $index, $left, string $class = '', ?array $getData = array()): string
    {
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
    /**
     * @param $listMenu
     * @return array
     */
    function getPermissionRouteList($listMenu): array
    {
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
    /**
     * @param $listMenu
     * @param $permissionRoute
     * @return array
     */
    function listGetPermission($listMenu, $permissionRoute): array
    {
        $result = [];
        if ($permissionRoute) {
            foreach ($listMenu as $list) {
                if ($list['type'] == 1) {
                    if (in_array($list['route'], $permissionRoute)) {
                        $result[] = $list;
                    }
                }
                else if ($list['type'] == 3) {
                    $result[] = $list;
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
