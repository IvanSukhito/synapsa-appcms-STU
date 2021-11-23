<?php

use Illuminate\Support\Facades\Route;
use Ramsey\Uuid\Uuid;

Route::group(['prefix' => env('ADMIN_URL'), 'middleware' => ['web']], function () use ($router) {

    $router->get('login', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@getLogin', 'middleware' => ['adminHaveLogin']])->name('admin.login');
    $router->post('login', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@postLogin', 'middleware' => ['adminHaveLogin']])->name('admin.login.post');
    $router->get('logout', ['uses' => 'App\Http\Controllers\Admin\AccessAdminController@doLogout'])->name('admin.logout');

    $router->group(['middleware' => ['adminLogin', 'preventBackHistory']], function () use ($router) {

        $router->group(['prefix' => 'profile'], function () use ($router) {
            $router->get('edit', ['uses'=>'App\Http\Controllers\Admin\ProfileController@getProfile'])->name('admin.get_profile');
            $router->post('edit', ['uses'=>'App\Http\Controllers\Admin\ProfileController@postProfile'])->name('admin.post_profile');
            $router->get('password', ['uses'=>'App\Http\Controllers\Admin\ProfileController@getPassword'])->name('admin.get_password');
            $router->post('password', ['uses'=>'App\Http\Controllers\Admin\ProfileController@postPassword'])->name('admin.post_password');
            $router->get('/', ['uses'=>'App\Http\Controllers\Admin\ProfileController@profile'])->name('admin.profile.index');
        });

        $router->group(['middleware' => ['adminAccessPermission']], function () use ($router) {
            $listRouter = [
                'App\Http\Controllers\Admin\SettingsController' => 'settings',
                'App\Http\Controllers\Admin\AdminController' => 'admin',
                'App\Http\Controllers\Admin\CustomerSupportController' => 'customer-support',
                'App\Http\Controllers\Admin\RoleController' => 'role',
                'App\Http\Controllers\Admin\UserController' => 'user',
                'App\Http\Controllers\Admin\ArticleController' => 'article',
                'App\Http\Controllers\Admin\BannerPageController' => 'banner',
                'App\Http\Controllers\Admin\ArticleCategoryController' => 'article-category',
                'App\Http\Controllers\Admin\FaqsController' => 'faqs',
                'App\Http\Controllers\Admin\KlinikController' => 'klinik',
                'App\Http\Controllers\Admin\ProductController' => 'product',
                'App\Http\Controllers\Admin\ProductClinicController' => 'product-clinic',
                'App\Http\Controllers\Admin\TransactionController' => 'transaction',
                'App\Http\Controllers\Admin\TransactionLabController' => 'transaction-lab',
                'App\Http\Controllers\Admin\TransactionDoctorController' => 'transaction-doctor',
                'App\Http\Controllers\Admin\TransactionProductController' => 'transaction-product',
                'App\Http\Controllers\Admin\ProductCategoryController' => 'product-category',
                'App\Http\Controllers\Admin\LabClinicController' => 'lab-clinic',
                'App\Http\Controllers\Admin\LabClinicScheduleController' => 'lab-clinic-schedule',
                'App\Http\Controllers\Admin\UsersController' => 'users',
                'App\Http\Controllers\Admin\UsersPatientController' => 'users-patient',
                'App\Http\Controllers\Admin\DoctorController' => 'doctor',
                'App\Http\Controllers\Admin\DoctorScheduleController' => 'doctor-schedule',
                'App\Http\Controllers\Admin\DoctorCategoryController' => 'doctor-category',
                'App\Http\Controllers\Admin\ServiceController' => 'service',
                'App\Http\Controllers\Admin\PaymentController' => 'payment',
                'App\Http\Controllers\Admin\ShippingController' => 'shipping',
                'App\Http\Controllers\Admin\AppointmentLabController' => 'appointment-lab',
                'App\Http\Controllers\Admin\AppointmentLabScheduleController' => 'appointment-lab-schedule',
                'App\Http\Controllers\Admin\AppointmentLabHomecareController' => 'appointment-lab-homecare',
                'App\Http\Controllers\Admin\AppointmentLabVisitController' => 'appointment-lab-visit',
//                'App\Http\Controllers\Admin\AppointmentDoctorVisitController' => 'appointment-doctor-visit-clinic',
//                'App\Http\Controllers\Admin\AppointmentDoctorTelemedController' => 'appointment-doctor-telemed-clinic',
//                'App\Http\Controllers\Admin\AppointmentDoctorHomecareController' => 'appointment-doctor-homecare-clinic',

                'App\Http\Controllers\Admin\AppointmentNurseController' => 'appointment-nurse',
                'App\Http\Controllers\Admin\ClinicInfoController' => 'clinic_info',
                'App\Http\Controllers\Admin\DoctorClinicController' => 'doctor_clinic',
                'App\Http\Controllers\Admin\UsersClinicController' => 'user-clinic',
                'App\Http\Controllers\Admin\TransactionLabAdminController' => 'transaction-lab-admin',
                'App\Http\Controllers\Admin\TransactionDoctorAdminController' => 'transaction-doctor-admin',
                'App\Http\Controllers\Admin\TransactionProductAdminController' => 'transaction-product-admin',
                'App\Http\Controllers\Admin\LabController' => 'lab',
                'App\Http\Controllers\Admin\LabScheduleController' => 'lab-schedule',
                'App\Http\Controllers\Admin\InvoiceController' => 'invoice',
                'App\Http\Controllers\Admin\AppointmentLabHomecareClinicController' => 'appointment-lab-homecare-clinic',
                'App\Http\Controllers\Admin\AppointmentLabVisitClinicController' => 'appointment-lab-visit-clinic',
            ];

            foreach ($listRouter as $controller => $linkName) {

                switch ($linkName) {
                    case 'lab-clinic-schedule':
                    case 'lab-schedule':
                        $router->post($linkName . '/{id}/updateSchedule',   $controller.'@update')->name('admin.' . $linkName . '.updateLab');
                        $router->get($linkName . '/create2', $controller.'@create2')->name('admin.' . $linkName . '.create2');
                        $router->post($linkName . '/store2', $controller.'@store2')->name('admin.' . $linkName . '.store2');
                        break;
                    case 'appointment-lab-homecare-clinic':
                    case 'appointment-lab-visit-clinic':
                    $router->get($linkName . '/{id}/approve',   $controller.'@approve')->name('admin.' . $linkName . '.approve');
                    $router->get($linkName . '/{id}/reject',   $controller.'@reject')->name('admin.' . $linkName . '.reject');
                    $router->get($linkName . '/{id}/upload-hasil-lab', $controller.'@uploadHasilLab')->name('admin.'. $linkName . '.uploadHasilLab');
                    $router->post($linkName . '/{id}/upload-hasil-lab', $controller.'@storeHasilLab')->name('admin.'. $linkName . '.storeHasilLab');
                        break;
                    case 'transaction-doctor':
                    case 'appointment-nurse':
                    case 'transaction-product':
                    case 'transaction-lab':
                        $router->get($linkName . '/{id}/approve',   $controller.'@approve')->name('admin.' . $linkName . '.approve');
                        $router->get($linkName . '/{id}/reject',   $controller.'@reject')->name('admin.' . $linkName . '.reject');
                        break;
                    case 'klinik':
                    case 'product':
                        $router->get($linkName . '/create2', $controller.'@create2')->name('admin.' . $linkName . '.create2');
                        $router->post($linkName . '/store2', $controller.'@store2')->name('admin.' . $linkName . '.store2');
                        break;
                    case 'product-clinic':
                        $router->get($linkName . '/create2', $controller.'@create2')->name('admin.' . $linkName . '.create2');
                        $router->post($linkName . '/store2', $controller.'@store2')->name('admin.' . $linkName . '.store2');
                        $router->get($linkName . '/get-product-synapsa', $controller.'@getProductSynapsa')->name('admin.' . $linkName . '.getProductSynapsa');
                        $router->get($linkName . '/create3', $controller.'@create3')->name('admin.' . $linkName . '.create3');
                        $router->post($linkName . '/{id}/store3', $controller.'@store3')->name('admin.' . $linkName . '.store3');
                        break;
                    case 'doctor_clinic':
                    case 'doctor':
                        $router->get($linkName . '/create2', $controller.'@create2')->name('admin.' . $linkName . '.create2');
                        $router->post($linkName . '/store2', $controller.'@store2')->name('admin.' . $linkName . '.store2');
                        $router->get($linkName . '/{id}/schedule',   $controller.'@schedule')->name('admin.' . $linkName . '.schedule');
                        $router->post($linkName . '/{id}/schedule',   $controller.'@storeSchedule')->name('admin.' . $linkName . '.storeSchedule');
                        $router->get($linkName . '/{id}/createschedule2', $controller.'@createschedule2')->name('admin.' . $linkName . '.createschedule2');
                        $router->post($linkName . '/{id}/storeschedule2', $controller.'@storeschedule2')->name('admin.' . $linkName . '.storeschedule2');
                        $router->post($linkName . '/{id}/schedule/{scheduleId}',   $controller.'@updateSchedule')->name('admin.' . $linkName . '.updateSchedule');
                        $router->delete($linkName . '/{id}/schedule/{scheduleId}',   $controller.'@destroySchedule')->name('admin.' . $linkName . '.destroySchedule');
                        break;

                }
                $router->get($linkName . '/data', $controller . '@dataTable')->name('admin.' . $linkName . '.dataTable');
                $router->resource($linkName, $controller, ['as' => 'admin']);
            }

        });

        $router->get('/', ['uses' => 'App\Http\Controllers\Admin\DashboardController@dashboard'])->name('admin');
        $router->get('/download', ['uses' => 'App\Http\Controllers\Admin\DashboardController@download'])->name('admin.download');

    });
});

Route::get('change-password', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@changeTokenPassword'])->name('web.changeTokenPassword');
Route::post('change-password', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@updatePassword'])->name('web.updatePassword');

Route::get('confirm-email', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@confirmEmail'])->name('web.confirmEmail');
Route::get('confirm-phone', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@confirmPhone'])->name('web.confirmPhone');

Route::get('/', ['uses' => 'App\Http\Controllers\Website\V1\GeneralController@xendit'])->name('web.xendit');

//UsersPatient
Route::get('/findCity', ['uses' => 'App\Http\Controllers\Admin\GeneralController@findCity'])->name('admin.findCity');
Route::get('/findDistrict', ['uses' => 'App\Http\Controllers\Admin\GeneralController@findDistrict'])->name('admin.findDistrict');
Route::get('/findSubDistrict', ['uses' => 'App\Http\Controllers\Admin\GeneralController@findSubDistrict'])->name('admin.findSubDistrict');
Route::get('/findProductSynapsa', ['uses' => 'App\Http\Controllers\Admin\GeneralController@findProductSynapsa'])->name('admin.findProductSynapsa');
Route::get('/appointment-lab-schedule', ['uses' => 'App\Http\Controllers\Admin\GeneralController@appointmentLabSchedule'])->name('admin.appointmentLabSchedule');

Route::get('test', function () {
    return view('welcome');
});
