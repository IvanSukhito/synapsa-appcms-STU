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
        for($i=1; $i<=10; $i++) {
            DB::table('users')->insertGetId([
                'klinik_id' => 1,
                'city_id' => 0,
                'district_id' => 0,
                'sub_district_id' => 0,
                'fullname' => 'DEMO '.$i,
                'address' => 'ALAMAT DEMO',
                'address_detail' => 'ALAMAT DETAIL DEMO',
                'email' => 'demo'.$i.'@mailinator.com',
                'password' => bcrypt('123456'),
                'patient' => 1,
                'doctor' => 0,
                'nurse' => 0,
                'verification_phone' => 1,
                'verification_email' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
        //Article Category
        for ($i=1; $i<=10; $i++) {
            DB::table('article_category')->insertGetId([
                'name' => 'DEMO '.$i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        //Article
        for($i=1; $i<=200; $i++) {
            DB::table('article')->insertGetId([
                'article_category_id' => rand(1,10),
                'title' => 'DEMO '.$i,
                'slugs' => 'DEMO '.$i,
                'thumbnail_img' => 'Images.Png',
                'image' => 'Images.Png',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                'preview' => '',
                'publish_status' => 2,
                'created_by' => 'demo'.$i.'@mailinator.com',
                'updated_by' => 'demo'.$i.'@mailinator.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
