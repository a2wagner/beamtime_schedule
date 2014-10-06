<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRunCoordinatorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('run_coordinators', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('beamtime_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->foreign('beamtime_id')->references('id')->on('beamtimes')->onDelete('cascade')->onUpdate('cascade');
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
		Schema::drop('run_coordinators');
	}

}
