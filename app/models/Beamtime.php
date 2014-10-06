<?php

class Beamtime extends \Eloquent {
	protected $fillable = ['name'];

	public static $rules = ['name' => 'required'];

	/**
	* A beamtime consists of many shifts
	*
	* @return Array of Shift objects
	*/
	public function shifts()
	{
		return $this->hasMany('Shift');
	}

	/**
	* A beamtime has several run coordinators (users)
	*
	* @return Array of User objects
	*/
	public function run_coordinators()
	{
		return $this->hasMany('User', 'run_coordinators');
	}

	/**
	* Create an array of days between a given start and end date
	*
	* @param DateTime $start, DateTime $end, int $duration
	* @return array of days between start and end date
	*/
	public function createShifts($_start, $end, $duration) 
	{
		$range = array();

#		if (is_string($start) === true)
#			$start = strtotime($start);
#		if (is_string($end) === true )
#			$end = strtotime($end);

#		if ($start > $end)
#			return createDateRangeArray($end, $start);

#		do {
#			$range[] = date/*_create*/('Y-m-d', $start);
#			$start = strtotime("+ 8 hours", $start);
#		} while($start < $end);

		$length = $duration;
		$start = clone($_start);

		while ($start < $end) {
			$begin = clone($start);  // start gets modified with the add method, store it in another variable for later (database) usage
			$dur = 'PT'.$length.'H';
			$start->add(new DateInterval($dur));  // add the legnth of the shifts in hours to the start date
			$interval = $begin->diff($end);
			// when there are less hours than the dedfined shift length remaining, change the length of this last shift to the remaining time
			if ($interval->d == 0 && $interval->h < $duration)
				$length = $interval->h;
			//echo $start->format('Y-m-d H:i:s') . " - duration " . $length . " hours<br />\n";
			$range[] = array('start' => $begin->format('Y-m-d H:i:s'), 'duration' => $length, 'n_crew' => '2');
		}

		return $range;
	}
}
