<?php

class Shift extends \Eloquent {
	/**
	 * General time (hour of day) to check if a given shift is a day shift
	 *
	 * @var int
	 */
	const DAY = 12;

	/**
	 * General time (hour of day) to check if a given shift is a day shift
	 *
	 * @var int
	 */
	const LATE = 20;

	/**
	 * General time (hour of day) to check if a given shift is a night shift
	 *
	 * @var int
	 */
	const NIGHT = 4;

	protected $fillable = ['beamtime_id', 'start', 'duration', 'n_crew', 'maintenance', 'remark'];

	// Do not use timestamps for this model
	public $timestamps = false;

	public static $rules = [
		'beamtime_id' => 'required',
		'start' => 'required|date',
		'duration' => 'required|integer|max:10',
		'n_crew' => 'required|integer|in:0,2',
	];

	/**
	* A shift belongs to one beamtime
	*
	* @return Beamtime
	*/
	public function beamtime()
	{
		return $this->belongsTo('Beamtime');
	}

	/**
	* A shift is taken by one or two users, many to many relation
	*
	* @return Array of User objects
	*/
	public function users()
	{
		return $this->belongsToMany('User');
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
	* Get the other user, if existent, from the current Shift object regarding to the given id
	*
	* @param int $id
	* @return User
	*/
	public function get_other_user($id)
	{
		return $this->users->filter(function($user) use($id)  // use the 'use' keyword to pass the $id to the closure
		{
			return $user->id != $id;
		})->first();
	}

	/**
	* Get the id of the other user, if existent, from the current Shift object regarding to the given id
	*
	* @param int $id
	* @return int
	*/
	public function get_other_user_id($id)
	{
		if (!$user = $this->get_other_user($id))
			return 0;
		else
			return $user->id;
	}

	/**
	* Check if the shift is in the specified year
	*
	* @param int $year
	* @return boolean
	*/
	public function is_year($year)
	{
		$date = new DateTime($this->start);
		return $date->format('Y') == $year;
	}

	/**
	* Check if a given date is between to other dates
	*
	* @param $start, $end, $date
	* @return boolean
	*/
	public static function date_in_range($start, $end, $date = 'now')
	{
		// convert non-timestamps to timestamps
		$start = is_int($start) || $start instanceof DateTime ? $start : strtotime($start);
		$end = is_int($end) || $start instanceof DateTime ? $end : strtotime($end);
		$date = is_int($date) || $start instanceof DateTime ? $date : strtotime($date);

		// check if start and end order is correct
		if ($start > $end)
			return $this->date_in_range($end, $start, $date);

		// check if the date is between start and end date
		return $date > $start && $date < $end;
	}

	/**
	* Check if the current shift is a day, late or night shift
	*
	* @return string
	*/
	public function type()
	{
		if ($this->is_day())
			return 'day';
		elseif ($this->is_late())
			return 'late';
		else
			return 'night';
	}

	/**
	* Check if the shift is during a specific time ($hour)
	*
	* @param int $hour
	* @return boolean
	*/
	public function check_time($hour)
	{
		if (!is_int($hour))
			return false;

		$start = new DateTime($this->start);
		$end = clone($start);
		$dur = 'PT' . $this->duration . 'H';
		$end->add(new DateInterval($dur));
		$date = clone($start);
		$date->setTime($hour, 00);
		if ($date < $start)
			$date->modify('+1 day');

		return $this->date_in_range($start, $end, $date);
	}

	/**
	* Check if the shift is a day shift
	*
	* @return boolean
	*/
	public function is_day()
	{
		return $this->check_time(self::DAY);
	}

	/**
	* Check if the shift is a late shift
	*
	* @return boolean
	*/
	public function is_late()
	{
		return $this->check_time(self::LATE);
	}

	/**
	* Check if the shift is a night shift
	*
	* @return boolean
	*/
	public function is_night()
	{
		return $this->check_time(self::NIGHT);
	}
}
