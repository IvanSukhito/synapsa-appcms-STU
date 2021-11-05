<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstallingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role')->insertGetId([
            'name' => 'Super Admin',
            'permission_data' =>  '{"super_admin":1,"admin":{"list":1,"create":1,"edit":1,"show":1,"destroy":1},"role":{"list":1,"create":1,"edit":1,"show":1,"destroy":1}}',
            'permission_route' => '["admin.admin.index","admin.admin.dataTable","admin.admin.create","admin.admin.store","admin.admin.edit","admin.admin.update","admin.admin.show","admin.admin.destroy","admin.role.index","admin.role.dataTable","admin.role.create","admin.role.store","admin.role.edit","admin.role.update","admin.role.show","admin.role.destroy"]',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('admin')->insertGetId([
            'name' => 'admin',
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'role_id' => 1,
            'status' => 80,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting')->insertGetId([
            'name' => 'IOS Version',
            'key' => 'ios-version',
            'value' => '1.0.0',
            'type' => 'text',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting')->insertGetId([
            'name' => 'IOS URL',
            'key' => 'ios-url',
            'value' => '',
            'type' => 'textarea',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting')->insertGetId([
            'name' => 'Android Version',
            'key' => 'android-version',
            'value' => '1.0.0',
            'type' => 'text',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting')->insertGetId([
            'name' => 'Android URL',
            'key' => 'android-url',
            'value' => '',
            'type' => 'textarea',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting')->insertGetId([
            'name' => 'Service Doctor',
            'key' =>  'service-doctor',
            'value' =>  '[1,2,3]',
            'type' => 'multiselect2',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('setting')->insertGetId([
            'name' => 'Service Lab',
            'key' =>  'service-lab',
            'value' => '[1,2]',
            'type' => 'multiselect2',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('setting')->insertGetId([
            'name' => 'Time Online Meeting (Minute)',
            'key' =>  'time-online-meeting',
            'value' => '30',
            'type' => 'number',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('setting')->insertGetId([
            'name' => 'Time Expired Transaction (Day)',
            'key' =>  'time-expired-transaction',
            'value' => '1',
            'type' => 'number',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

    }
}
