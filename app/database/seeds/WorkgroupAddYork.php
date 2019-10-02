<?php

class WorkgroupAddYork extends Seeder {

	public function run()
	{
		if (!Workgroup::whereName('University of York')->count()) {
			$group = Workgroup::create([
				'name' => 'University of York',
				'country' => 'England',
				'short' => 'York',
				'region' => 1
			]);
		}
	}

}
