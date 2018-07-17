<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixMysql57Changes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE `users` CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL;');
		DB::statement('ALTER TABLE `users` CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL;');
		DB::statement('ALTER TABLE `users` CHANGE COLUMN `last_login` `last_login` TIMESTAMP NULL DEFAULT NULL;');
		DB::statement('ALTER TABLE `users` CHANGE COLUMN `start_date` `start_date` TIMESTAMP NULL DEFAULT NULL;');
		DB::statement('ALTER TABLE `users` CHANGE COLUMN `retire_date` `retire_date` TIMESTAMP NULL DEFAULT NULL;');

		DB::statement('ALTER TABLE `beamtimes` CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL;');
		DB::statement('ALTER TABLE `beamtimes` CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL;');

		DB::statement('ALTER TABLE `radiation_instructions` CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL;');
		DB::statement('ALTER TABLE `radiation_instructions` CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL;');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}

}
