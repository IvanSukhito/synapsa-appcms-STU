<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Users
        for ($i = 1; $i <= 10; $i++) {
            for ($i = 1; $i <= 10; $i++) {
                DB::table('klinik')->insertGetId([
                    'name' => 'Klinik ' . $i,
                    'status' => 80
                ]);
            }
            for ($i = 1; $i <= 50; $i++) {
                DB::table('users')->insertGetId([
                    'klinik_id' => rand(1, 10),
                    'city_id' => 0,
                    'district_id' => 0,
                    'sub_district_id' => 0,
                    'fullname' => 'DEMO ' . $i,
                    'address' => 'ALAMAT DEMO',
                    'address_detail' => 'ALAMAT DETAIL DEMO',
                    'email' => 'demo' . $i . '@mailinator.com',
                    'password' => bcrypt('123456'),
                    'patient' => rand(0, 1),
                    'doctor' => rand(0, 1),
                    'nurse' => 0,
                    'verification_phone' => 1,
                    'verification_email' => 1,
                    'status' => '80',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            //Product Category
            for ($i = 1; $i <= 10; $i++) {
                DB::table('product_category')->insertGetId([
                    'name' => 'PRODUCT_CATEGORY ' . $i,
                    'status' => 80,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            for ($i = 1; $i <= 10; $i++) {
                DB::table('faqs')->insertGetId([
                    'question' => 'question' . $i,
                    'answer' => 'answer' . $i,
                    'orders' => 1,
                    'created_by' => 'Demo99@mailinator.com',
                    'updated_by' => 'Demo99@mailinator.com',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            for ($i = 1; $i <= 5; $i++) {
                DB::table('sliders')->insertGetId([
                    'title' => 'demo' . $i,
                    'image' => '',
                    'target' => '',
                    'orders' => 1,
                    'status' => 80,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            //Product
            for ($i = 1; $i <= 100; $i++) {
                DB::table('product')->insertGetId([
                    'product_category_id' => rand(1, 10),
                    'sku' => 'DEMO ' . $i,
                    'name' => 'DEMO ' . $i,
                    'image' => '',
                    'price' => '99000',
                    'unit' => 'DEMO ' . $i,
                    'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                    'stock' => 99,
                    'stock_flag' => 2,
                    'status' => 80,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            //Article Category
            for ($i = 1; $i <= 10; $i++) {
                DB::table('article_category')->insertGetId([
                    'name' => 'ARTICLE_CATEGORY ' . $i,
                    'status' => 80,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            //Article
            for ($i = 1; $i <= 200; $i++) {
                DB::table('article')->insertGetId([
                    'article_category_id' => rand(1, 10),
                    'title' => 'DEMO ' . $i,
                    'slugs' => 'DEMO ' . $i,
                    'thumbnail_img' => '',
                    'image' => '',
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                    'preview' => '',
                    'publish_status' => rand(0, 1),
                    'publish_date' => Carbon::now(),
                    'created_by' => 'demo' . $i . '@mailinator.com',
                    'updated_by' => 'demo' . $i . '@mailinator.com',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            //Doctor Category
            for ($i = 1; $i <= 10; $i++) {
                DB::table('doctor_category')->insertGetId([
                    'name' => 'DOCTOR_CATEGORY' . $i,
                    'icon_img' => '',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            //Doctor Schedule
            for ($i = 1; $i <= 10; $i++) {
                DB::table('doctor_schedule')->insertGetId([
                    'doctor_id' => rand(1, 10),
                    'day' => rand(1, 7),
                    'time_start' => rand(8, 11) . ':00 ',
                    'time_end' => rand(8, 11) . ':00',
                    'book' => rand(0, 1) == 1 ? 80 : 99,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            //Doctor
            for ($i = 1; $i <= 100; $i++) {
                DB::table('doctor')->insertGetId([
                    'user_id' => rand(1, 10),
                    'doctor_category_id' => rand(1, 10),
                    'price' => '99000',
                    'formal_edu' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                    'nonformal_edu' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            //Lab
            //for ($i = 1; $i <= 100; $i++) {
            //    DB::table('lab')->insertGetId([
            //        'parent_id' => rand(0, 2),
            //        'name' => 'DEMO ' . $i,
            //        'price' => '99000',
            //        'thumbnail_img' => '',
            //        'image' => '',
            //        'desc_lab' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            //        'desc_benefit' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            //        'desc_preparation' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            //        'recommended_for' => json_encode(['Pria', 'Wanita', 'Lansia', 'Anak-anak']),
            //        'created_at' => Carbon::now(),
            //        'updated_at' => Carbon::now(),
            //    ]);
            //}
            //Lab Product
           // for ($i = 1; $i <= 100; $i++) {
           //     DB::table('lab_product')->insertGetId([
           //         'parent_id' => 0,
           //         'title' => 'test' . $i,
           //         'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
           //         'image' => '',
           //         'benefit' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
           //         'preparation' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
           //         'created_at' => Carbon::now(),
           //         'updated_at' => Carbon::now(),
           //     ]);
           // }

            //Notif
            //Notifications
            for ($i = 1; $i <= 100; $i++) {
                DB::table('notifications')->insertGetId([
                    'user_id' => rand(1, 10),
                    'title' => 'notif' . $i,
                    'message' => 'notifikasi' . $i,
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                    'target' => '',
                    'is_read' => rand(0, 1),
                    'type' => 'notifications',
                    'date' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            //Shipping
                for ($i = 1; $i <= 10; $i++) {
                    DB::table('shipping')->insertGetId([
                        'name' => 'Shipping' . $i,
                        'icon' => '',
                        'settings' => '',
                        'orders' => rand(0, 1),
                        'status' => rand(0, 1),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
                //Payment
                for ($i = 1; $i <= 5; $i++) {
                    DB::table('payment')->insertGetId([
                        'name' => 'Payment' . $i,
                        'icon_img' => '',
                        'orders' => rand(0, 1),
                        'settings' => '',
                        'type' => rand(0, 4),
                        'status' => rand(0, 1),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
