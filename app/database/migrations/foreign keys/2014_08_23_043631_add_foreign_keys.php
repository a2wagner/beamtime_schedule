<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeys extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function($table)
		{
			$table->foreign('institute_id')->references('id')->on('institutes');
		});
		
		Schema::table('shifts', function($table)
		{
			$table->foreign('beamtime_id')->references('id')->on('beamtimes')->onDelete('cascade')->onUpdate('cascade');
		});

		Schema::table('run_coordinators', function($table)
		{
			$table->foreign('beamtime_id')->references('id')->on('beamtimes')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
		});

		Schema::table('shift_user', function($table)
		{
			$table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function($table)
		{
			$table->dropForeign('institute_id');
		});

		Schema::table('shifts', function($table)
		{
			$table->dropForeign('beamtime_id');
		});

		Schema::table('run_coordinators', function($table)
		{
			$table->dropForeign('beamtime_id');
			$table->dropForeign('user_id');
		});

		Schema::table('shift_user', function($table)
		{
			$table->dropForeign('shift_id');
			$table->dropForeign('user_id');
		});
	}

}
