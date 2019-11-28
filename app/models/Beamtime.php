<?php

class Beamtime extends \Eloquent {
	/**
	 * Number of days working groups from Europe have to wait after subscription started before they're allowed to subscribe to shifts
	 *
	 * @var int
	 */
	const SUBSCRIPTION_WAITING_DAYS_EUROPE = 0;

	/**
	 * Number of days the local working group has to wait after subscription started before they're allowed to subscribe to shifts
	 *
	 * @var int
	 */
	const SUBSCRIPTION_WAITING_DAYS_LOCAL = 0;

	protected $fillable = ['name', 'description', 'enforce_subscription', 'subscription_start', 'enforce_rc', 'experience_block'];

	public static $rules = ['name' => 'required|max:100', 'description' => 'max:500',
		'enforce_subscription' => 'boolean', 'subscription_start' => 'date',
		'enforce_rc' => 'boolean', 'experience_block' => 'boolean',
		'weekday_crew1' => 'boolean', 'day_late_crew1' => 'boolean'];

	public $errors;

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
	public function rcshifts()
	{
		return $this->hasMany('RCShift');
	}

	/**
	* Return a collection of run coordinators for the current beamtime
	*
	* @return Array of unique User objects
	*/
	public function run_coordinators()
	{
		return $this->rcshifts->user->unique();
	}

	/**
	* Validation of the filled attributes concerning the defined rules and messages for the check
	*
	* @return bool of validation check
	*/
	public function isValid()
	{
		$validation = Validator::make($this->attributes, static::$rules);

		if ($validation->passes())
			return true;

		$this->errors = $validation->messages();

		return false;
	}

	/**
	 * Use the custom collection that allows tapping
	 *
	 * @return UtilityCollection
	 */
	public function newCollection(array $models = array())
	{
		return new UtilityCollection($models);
	}

	/**
	* Create an array of days between a given start and end date
	*
	* @param DateTime $start, DateTime $end, int $duration
	* @return two-dimensional array of array of shifts and array of run coordinator shifts between start and end date
	*/
	public function createShifts($_start, $end, $duration)
	{
		// check if the start date is smaller than the end date, otherwise switch positions
		if ($_start > $end)
			return $this->createShifts($end, $start, $duration);

		$shifts = array();
		$rc_shifts = array();

		// normal shifts
		$length = $duration;
		$start = clone($_start);
		// run coordinator shifts
		$rc_default_length = RCShift::DURATION;
		$rc_length = $rc_default_length;
		$rc_start = new DateTime($start->format('Y-m-d ' . RCShift::START . ':00:00'));
		$rc_diff_start = $start->diff($rc_start);
		$rc_length_first = 0;
		// check if the difference between the beamtime start and the start of the run coordinator shift pattern doesn't match
		if ($rc_diff_start->h != 0) {
			// length in hours the first shift has to have to match the start and duration pattern specified in RC_Shift
			$rc_length_first = $rc_diff_start->h % $rc_length;
			// if the difference is negative (beamtime start before $rc_start) --> subtract the calculated value stored in $rc_length_first from the specified RC length shift
			if ($rc_diff_start->invert == 1)
				$rc_length_first = $rc_length - $rc_length_first;
		}

		/* create the normal shifts */
		// PHP and DateTime/DateInterval has a funny bug regarding DST changes. Though the transition from "winter" to summer time works fine,
		// the additional hour by switching end of October produces some funny problems with PHP. Fix this by checking if we switched from
		// summertime to wintertime and add one additional hour manually. The check following returns 1 or 0. Exploit this as some int by
		// incrementing $summertime to 2 and use this to check if the additional hour has been added.
		$summertime = date("I", $start->getTimestamp());
		while ($start < $end) {
			$summertime = date("I", $start->getTimestamp());
			$begin = clone($start);  // start gets modified with the add method, store it in another variable for later (database) usage
			$dur = 'PT' . $length . 'H';
			$start->add(new DateInterval($dur));  // add the legnth of the shifts in hours to the start date
			if ($summertime && !date("I", $start->getTimestamp())) {
				$summertime++;
				$start->add(new DateInterval('PT1H'));
			}
			$interval = $begin->diff($end);
			// when there are less hours than the defined shift length remaining, change the length of this last shift to the remaining time
			if ($interval->d == 0 && $interval->h < $duration)
				$length = $interval->h;
			//echo $start->format('Y-m-d H:i:s') . " - duration " . $length . " hours<br />\n";
			$shifts[] = array('start' => $begin->format('Y-m-d H:i:s'), 'duration' => ($summertime === 2) ? $length+1 : $length, 'n_crew' => '2');
		}

		/* create the run coordinator shifts now */
		// first set $start back to the initial beamtime start
		$start = clone($_start);
		// create shifts
		while ($start < $end) {
			$summertime = date("I", $start->getTimestamp());
			$begin = clone($start);  // start gets modified with the add method, store it in another variable for later (database) usage
			// in case the first run coordinator shift must have a different length to match the usual pattern, changed it
			if ($rc_length_first != 0)
				$rc_length = $rc_length_first;
			$dur = 'PT' . $rc_length . 'H';
			$start->add(new DateInterval($dur));  // add the legnth of the shifts in hours to the start date
			if ($summertime && !date("I", $start->getTimestamp())) {
				$summertime++;
				$start->add(new DateInterval('PT1H'));
			}
			$interval = $begin->diff($end);
			// when there are less hours than the defined rc shift length remaining, change the length of this last shift to the remaining time
			if ($interval->d == 0 && $interval->h < $rc_length)
				$rc_length = $interval->h;

			$rc_shifts[] = array('start' => $begin->format('Y-m-d H:i:s'), 'duration' => ($summertime === 2) ? $rc_length+1 : $rc_length);

			// if this is the first loop run and the first shift length is different, change $rc_length so that the stored value in the array is correct
			if ($rc_length_first != 0) {
				$rc_length = $rc_default_length;  // set the length back to the usual duration of rc shifts
				$rc_length_first = 0;
			}
		}

		return array('normal' => $shifts, 'rc' => $rc_shifts);
	}

	/**
	* Check if the beamtime is in the specified year
	*
	* @param int $year
	* @return boolean
	*/
	public function is_year($year)
	{
		if (!$this->shifts->count())
			return NULL;
		return $this->shifts->first()->is_year($year);
	}

	/**
	* Check if the beamtime is in the specified array of years
	*
	* @param array $years
	* @return boolean
	*/
	public function is_in_years($years)
	{
		foreach ($years as $year)
			if ($this->shifts->first()->is_year($year))
				return true;

		// if the if condition above was not true, the beamtime lies not in the given array of years
		return false;
	}

	/**
	* Check if the beamtime is in the specified range of years
	*
	* @param int $begin, int $end
	* @return boolean
	*/
	public function is_in_range($begin, $end)
	{
		// check order of range and correct if necessary
		$start = $begin;
		if ($begin > $end) {
			$start = $end;
			$end = $begin;
		}

		for ($year = $start; $year <= $end; $year++)
			if ($this->shifts->first()->is_year($year))
				return true;

		// if the if condition above was not true, the beamtime lies not in the given range
		return false;
	}

	/**
	 * Return start of the beamtime
	 *
	 * @return DateTime
	 */
	public function start()
	{
		if (!$this->shifts->count())
			return NULL;
		return new DateTime($this->shifts->first()->start);
	}

	/**
	 * Return start string of the beamtime
	 *
	 * @return string
	 */
	public function start_string()
	{
		if (!$this->shifts->count())
			return NULL;
		return $this->shifts->first()->start;
	}

	/**
	 * Return end of the beamtime
	 *
	 * @return DateTime
	 */
	public function end()
	{
		if (!$this->shifts->count())
			return NULL;
		return $this->shifts->last()->end();
	}

	/**
	 * Return end string of the beamtime
	 *
	 * @return string
	 */
	public function end_string()
	{
		if (!$this->shifts->count())
			return NULL;
		return $this->shifts->last()->end()->format('Y-m-d H:i:s');
	}

	/**
	 * Return a string about the shift subscription enforcement (incl. date if activated)
	 *
	 * @return string
	 */
	public function subscription_start_string()
	{
		if (!$this->enforce_subscription)
			return 'Subscription date enforcement is not activated';

		return 'Subscription date enforcement is activated, shift subscriptions are allowed starting at ' . $this->subscription_start;
	}

	/**
	 * Check if a user is allowed to subscribe to shifts of the current beamtime
	 *
	 * @param User $user
	 * @return boolean
	 */
	public function subscription_allowed($user)
	{
		if (!$this->enforce_subscription)
			return true;

		// run coordinators are allowed to subscribe anytime
		if ($user->isRunCoordinator() && $this->run_coordinators()->contains($user))
			return true;

		$now = new DateTime();
		$start = new DateTime($this->subscription_start);
		// apply subscription rules (TODO: define rulesets?)
		if ($user->workgroup->region === Workgroup::EUROPE)
			$start->modify('+' . self::SUBSCRIPTION_WAITING_DAYS_EUROPE . ' day');
		if ($user->workgroup->region === Workgroup::LOCAL)
			$start->modify('+' . self::SUBSCRIPTION_WAITING_DAYS_LOCAL . ' days');

		if ($now < $start)
			return false;

		return true;
	}
}
