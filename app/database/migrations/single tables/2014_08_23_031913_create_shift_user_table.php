<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shift_user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('shift_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->foreign('shift_id')->references('id')->on('shifts');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shift_user');
	}

}
