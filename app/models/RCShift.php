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
	* @param $start, $end, $ref_start, $ref_end
	* @return boolean
	*/
	public static function date_overlap($start, $end, $ref_start, $ref_end)
	{
		// convert non-timestamps to timestamps
		$start = is_int($start) || $start instanceof DateTime ? $start->getTimeStamp() : strtotime($start);
		$end = is_int($end) || $end instanceof DateTime ? $end->getTimeStamp() : strtotime($end);
		$ref_start = is_int($ref_start) || $ref_start instanceof DateTime ? $ref_start->getTimeStamp() : strtotime($ref_start);
		$ref_end = is_int($ref_end) || $ref_end instanceof DateTime ? $ref_end->getTimeStamp() : strtotime($ref_end);

		// check if start and end order is correct
		if ($start > $end)
			return $this->date_in_range($end, $start, $ref_start, $ref_end);

		// figure out which is the later start time
		$lastStart = $start >= $ref_start ? $start : $ref_start;

		// figure out which is the earlier end time
		$firstEnd = $end <= $ref_end ? $end : $ref_end;

		// get the difference in minutes between those two
		$overlap = floor(($firstEnd - $lastStart)/60);
		//dd($overlap);

		// If the answer is greater than 0 use it.
		// If not, there is no overlap.
		return $overlap > 0 ? $overlap : 0;
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
	* Check if the overlap between two time ranges is greater than or equal to 50%
	*
	* @param int $hour_start
	* @param int $hour_end
	* @param int $add_day (optional)
	* @return boolean
	*/
	public function check_time($hour_start, $hour_end, $add_day = 0)
	{
		if (!is_int($hour_start) || !is_int($hour_end))
			return false;

		$start = new DateTime($this->start);
		$end = $this->end();
		$ref_start = clone($start);
		$ref_end = clone($start);
		$ref_start->setTime($hour_start, 00);
		$ref_end->setTime($hour_end, 00);
		$ref_end->modify('+'.$add_day.' day');

		// calculate the percental overlap
		return $this->date_overlap($start, $end, $ref_start, $ref_end)/($this->duration*60) >= 0.5;
	}

	/**
	* Check if the shift is a day shift
	*
	* @return boolean
	*/
	public function is_day()
	{
		return $this->check_time(self::START, self::START + self::DURATION);
	}

	/**
	* Check if the shift is a night shift
	*
	* @return boolean
	*/
	public function is_night()
	{
		return $this->check_time(self::START + self::DURATION, self::START, 1);
	}

	/**
	 * Return end time of the shift
	 *
	 * @return DateTime
	 */
	public function end()
	{
		$date = new DateTime($this->start);
		$date->add(new DateInterval('PT' . $this->duration . 'H'));
		return $date;
	}
}
