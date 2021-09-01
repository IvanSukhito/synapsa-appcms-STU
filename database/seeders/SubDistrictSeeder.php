<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Flynsarmy\CsvSeeder\CsvSeeder;

class SubDistrictSeeder extends CsvSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function __construct()
	{
		$this->table = 'sub_district';
		$this->filename = base_path().'/database/seeders/csv/sub_district.csv';
	}

	public function run()
	{
		// Recommended when importing larger CSVs
		DB::disableQueryLog();

		// Uncomment the below to wipe the table clean before populating
		DB::table($this->table)->truncate();

		parent::run();
	}
}
