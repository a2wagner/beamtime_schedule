<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('first_name', 50);
			$table->string('last_name', 50);
			$table->string('username', 10)->unique();
			$table->string('email', 30)->unique();
			$table->string('password', 80);
			$table->integer('institute_id')->unsigned();
			$table->string('phone_institute', 30);
			$table->string('phone_private', 30);
			$table->string('phone_mobile', 30);
			$table->tinyInteger('rating')->unsigned();
			$table->boolean('isAdmin');
            $table->string('remember_token', 100)->nullable();
			$table->timestamps();
			//$table->foreign('institute_id')->references('id')->on('institutes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			Schema::drop('users');
		});
	}

}
