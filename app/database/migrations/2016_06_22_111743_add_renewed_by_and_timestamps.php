<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRenewedByAndTimestamps extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('radiation_instructions', function(Blueprint $table)
		{
			$table->integer('renewed_by')->unsigned()->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('radiation_instructions', function(Blueprint $table)
		{
			$table->dropColumn('renewed_by');
			$table->dropTimestamps();
		});
	}

}
