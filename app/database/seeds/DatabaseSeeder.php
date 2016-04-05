<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('WorkgroupTableSeeder');
		$this->command->info('Workgroups added to the database');
	}

}

class WorkgroupTableSeeder extends Seeder {

	public function run()
	{
		DB::table('workgroups')->delete();

		$group = Workgroup::create([
			'name' => 'University of Mainz', 
			'country' => 'Germany', 
			'short' => 'Mainz'
		]);

		$group = Workgroup::create([
			'name' => 'University of Glasgow', 
			'country' => 'Scotland', 
			'short' => 'Glasgow'
		]);

		$group = Workgroup::create([
			'name' => 'University of Basel', 
			'country' => 'Switzerland', 
			'short' => 'Basel'
		]);

		$group = Workgroup::create([
			'name' => 'University of Edinburgh', 
			'country' => 'Scotland', 
			'short' => 'Edinburgh'
		]);

		$group = Workgroup::create([
			'name' => 'INFN Pavia', 
			'country' => 'Italy', 
			'short' => 'Pavia'
		]);

		$group = Workgroup::create([
			'name' => 'University of Bonn', 
			'country' => 'Germany', 
			'short' => 'Bonn'
		]);

		$group = Workgroup::create([
			'name' => 'George Washington University', 
			'country' => 'USA', 
			'short' => 'GWU'
		]);

		$group = Workgroup::create([
			'name' => 'University of Regina', 
			'country' => 'Canada', 
			'short' => 'Regina'
		]);

		$group = Workgroup::create([
			'name' => 'Saint Mary\'s University', 
			'country' => 'Canada', 
			'short' => 'SMU'
		]);

		$group = Workgroup::create([
			'name' => 'Mount Allison University', 
			'country' => 'Canada', 
			'short' => 'MTA'
		]);

		$group = Workgroup::create([
			'name' => 'Hebrew University of Jerusalem', 
			'country' => 'Israel', 
			'short' => 'HUJI'
		]);

		$group = Workgroup::create([
			'name' => 'INR Moscow', 
			'country' => 'Russia', 
			'short' => 'INR'
		]);

		$group = Workgroup::create([
			'name' => 'JINR Dubna', 
			'country' => 'Russia', 
			'short' => 'JINR'
		]);

		$group = Workgroup::create([
			'name' => 'LPI Moscow', 
			'country' => 'Russia', 
			'short' => 'LPI'
		]);

		$group = Workgroup::create([
			'name' => 'NPI Gatchina', 
			'country' => 'Russia', 
			'short' => 'NPI'
		]);

		$group = Workgroup::create([
			'name' => 'RBI Zagreb', 
			'country' => 'Croatia', 
			'short' => 'RBI'
		]);

		$group = Workgroup::create([
			'name' => 'Kent State University', 
			'country' => 'USA', 
			'short' => 'Kent'
		]);

		$group = Workgroup::create([
			'name' => 'University of Lund', 
			'country' => 'Sweden', 
			'short' => 'Lund'
		]);

		$group = Workgroup::create([
			'name' => 'The Catholic University of America', 
			'country' => 'USA', 
			'short' => 'CUA'
		]);
	}

}
