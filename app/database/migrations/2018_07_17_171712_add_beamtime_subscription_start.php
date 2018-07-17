<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBeamtimeSubscriptionStart extends Migration {

	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::table('beamtimes', function(Blueprint $table)
		{
			$table->boolean('enforce_subscription')->default(false);
			$table->dateTime('subscription_start')->nullable()->default(NULL);
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
			$table->dropColumn('enforce_subscription');
			$table->dropColumn('subscription_start');
		});
	}

}
