<?php

use Illuminate\Database\Migrations\Migration;

class WorkgroupsAddYork extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Workgroup::all()->count() &&  // make sure db:seed ran already to prevent just adding this workgroup while migrating
				!Workgroup::whereName('University of York')->count()) {
			Artisan::call('db:seed', [
				'--class' => 'WorkgroupAddYork',
				'--force' => true
			]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$group = Workgroup::whereName('University of York')->first();
		if (!is_null($group))  // workgroup not found in case the seeder didn't run
			$group->delete();
	}

}
