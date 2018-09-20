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
		if (!Workgroup::whereName('University of York')->count()) {
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
		$group->delete();
	}

}
