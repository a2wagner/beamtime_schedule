<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabase extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('workgroups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 50);
			$table->string('country', 50)->nullable();
			$table->string('short', 15)->nullable();
		});

		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('first_name', 50);
			$table->string('last_name', 50);
			$table->string('username', 10)->unique();
			$table->string('email', 30)->unique();
			$table->string('password', 80);
			$table->integer('workgroup_id')->unsigned()->index();
			$table->string('phone_institute', 30)->nullable();
			$table->string('phone_private', 30)->nullable();
			$table->string('phone_mobile', 30)->nullable();
			$table->tinyInteger('rating')->unsigned();
			//TODO: maybe replace enabled, isAdmin with unsigned tinyInteger role: 0 -> !enabled, 1 -> enabled, 2 -> run coordinator, 3 -> admin, ... ?  --> remove run_coordinators table ?
			$table->boolean('isAdmin')->default(false);
			$table->boolean('enabled')->default(false);
            $table->string('remember_token', 100)->nullable();
			$table->timestamps();
			$table->foreign('workgroup_id')->references('id')->on('workgroups');
		});

		Schema::create('beamtimes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->timestamps();
		});

		Schema::create('shifts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('beamtime_id')->unsigned()->index();
			$table->dateTime('start');
			$table->tinyInteger('duration')->unsigned();
			$table->tinyInteger('n_crew')->unsigned();
			$table->boolean('maintenance')->default(false);
			$table->string('remark', 200)->nullable();
			$table->foreign('beamtime_id')->references('id')->on('beamtimes')->onDelete('cascade')->onUpdate('cascade');
		});

		Schema::create('swaps', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('hash', 20)->unique();
			$table->integer('user_id')->unsigned()->index();
			$table->integer('original_shift_id')->unsigned()->index();
			$table->integer('request_shift_id')->unsigned()->index();
			$table->integer('request_user_id')->unsigned()->index()->nullable;
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('original_shift_id')->references('id')->on('shifts')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('request_shift_id')->references('id')->on('shifts')->onDelete('cascade')->onUpdate('cascade');
		});

		Schema::create('run_coordinators', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('beamtime_id')->unsigned()->index();
			$table->integer('user_id')->unsigned()->index();
			$table->foreign('beamtime_id')->references('id')->on('beamtimes')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
		});

		Schema::create('shift_user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('shift_id')->unsigned()->index();
			$table->integer('user_id')->unsigned()->index();
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
		Schema::drop('shift_user');
		Schema::drop('run_coordinators');
		Schema::drop('shifts');
		//Schema::drop('swaps');
		Schema::drop('beamtimes');
		Schema::drop('users');
		Schema::drop('workgroups');
	}

}
