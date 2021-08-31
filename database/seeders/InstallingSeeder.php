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
            'status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

    }
}
