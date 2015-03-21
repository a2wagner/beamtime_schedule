<?php

class RCShift extends Eloquent
{
	/**
	 * General length for run coordinator shifts
	 *
	 * @var int
	 */
	const DURATION = 12;

	/**
	 * General starting time for run coordinator shifts (time in hours)
	 *
	 * @var int
	 */
	const START = 8;

	/**
	 * General time (hour of day) to check if a given shift is a day shift
	 *
	 * @var int
	 */
	const DAY = 14;

	/**
	 * General time (hour of day) to check if a given shift is a night shift
	 *
	 * @var int
	 */
	const NIGHT = 2;

	// Do not use timestamps for this model
	public $timestamps = false;

	protected $fillable = ['beamtime_id', 'start', 'duration'];

	public static $rules = [
		'beamtime_id' => 'required',
		'start' => 'required|date',
		'duration' => 'required|integer|max:12',
	];

	/**
	* A run coordinator shift belongs to one beamtime
	*
	* @return Beamtime
	*/
	public function beamtime()
	{
		return $this->belongsTo('Beamtime');
	}

	/**
	* A RC shift is taken by one run coordinator (extra pivot table used)
	*
	* @return Array of User objects
	*/
	public function user()
	{
		return $this->belongsToMany('User', 'rc_shift_user', 'rc_shift_id', 'user_id');
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
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'rc_shifts';

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
	* Check if the run coordinator shift is a day or night shift
	*
	* @return string
	*/
	public function type()
	{
		if ($this->is_day())
			return 'day';
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
	* Check if the shift is a night shift
	*
	* @return boolean
	*/
	public function is_night()
	{
		return $this->check_time(self::NIGHT);
	}
}
