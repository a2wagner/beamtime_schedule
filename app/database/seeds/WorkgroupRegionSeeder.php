<?php

class WorkgroupRegionSeeder extends Seeder {

	public function run()
	{
		$group = Workgroup::whereName('University of Mainz')->first();
		$group->region = 0;
		$group->save();

		$group = Workgroup::whereName('University of Glasgow')->first();
		$group->region = 1;
		$group->save();

		$group = Workgroup::whereName('University of Basel')->first();
		$group->region = 1;
		$group->save();

		$group = Workgroup::whereName('University of Edinburgh')->first();
		$group->region = 1;
		$group->save();

		$group = Workgroup::whereName('INFN Pavia')->first();
		$group->region = 1;
		$group->save();

		$group = Workgroup::whereName('University of Bonn')->first();
		$group->region = 1;
		$group->save();

		$group = Workgroup::whereName('George Washington University')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('University of Regina')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('Saint Mary\'s University')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('Mount Allison University')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('Hebrew University of Jerusalem')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('INR Moscow')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('JINR Dubna')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('LPI Moscow')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('NPI Gatchina')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('RBI Zagreb')->first();
		$group->region = 1;
		$group->save();

		$group = Workgroup::whereName('Kent State University')->first();
		$group->region = 2;
		$group->save();

		$group = Workgroup::whereName('University of Lund')->first();
		$group->region = 1;
		$group->save();

		$group = Workgroup::whereName('The Catholic University of America')->first();
		$group->region = 2;
		$group->save();
	}

}
