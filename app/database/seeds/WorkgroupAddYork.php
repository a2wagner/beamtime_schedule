<?php

class WorkgroupAddYork extends Seeder {

	public function run()
	{
		$group = Workgroup::create([
			'name' => 'University of York',
			'country' => 'England',
			'short' => 'York',
			'region' => 1
		]);
	}

}
