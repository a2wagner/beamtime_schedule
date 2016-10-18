<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnforceRcField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('beamtimes', function(Blueprint $table)
		{
			$table->boolean('enforce_rc')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('beamtimes', function(Blueprint $table)
		{
			$table->dropColumn('enforce_rc');
		});
	}

}
