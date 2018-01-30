<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExperienceBlockField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('beamtimes', function(Blueprint $table)
		{
			$table->boolean('experience_block')->default(false);
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
			$table->dropColumn('experience_block');
		});
	}

}
