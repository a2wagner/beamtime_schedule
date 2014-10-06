<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shifts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('beamtime_id')->unsigned();
			$table->dateTime('start');
			$table->tinyInteger('duration')->unsigned();
			$table->tinyInteger('n_crew')->unsigned();
			$table->boolean('maintenance');
			$table->string('remark');
			$table->foreign('beamtime_id')->references('id')->on('beamtimes')->onDelete('cascade')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shifts');
	}

}
