<?php

class Shift extends \Eloquent {
	/**
	 * General time (hour of day) for the start of a day shift
	 *
	 * @var int
	 */
	const DAY_START = 8;

	/**
	 * General time (hour of day) for the end of a day shift
	 *
	 * @var int
	 */
	const DAY_END = 16;

	/**
	 * General time (hour of day) for the start of a late shift
	 *
	 * @var int
	 */
	const LATE_START = 16;

	/**
	 * General time (hour of day) for the end of a late shift
	 *
	 * @var int
	 */
	const LATE_END = 0;

	/**
	 * General time (hour of day) for the start of a night shift
	 *
	 * @var int
	 */
	const NIGHT_START = 0;

	/**
	 * General time (hour of day) for the end of a night shift
	 *
	 * @var int
	 */
	const NIGHT_END = 8;

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
	* Check if a specific date is during the shift
	*
	* @param $date
	* @return boolean
	*/
	public function is_inside($date)
	{
		$start = strtotime($this->start);
		$end = $this->end()->getTimeStamp();
		$date = is_int($date) || $date instanceof DateTime ? $date->getTimeStamp() : strtotime($date);
		return $date > $start && $date < $end;
	}

	/**
	* Check if the shift is the current one
	*
	* @return boolean
	*/
	public function is_current()
	{
		return $this->is_inside('now');
	}

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
			return $this->date_overlap($end, $start, $ref_start, $ref_end);

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
		return $this->check_time(self::DAY_START, self::DAY_END);
	}

	/**
	* Check if the shift is a late shift
	*
	* @return boolean
	*/
	public function is_late()
	{
		// add 1 day on end date because it is 0 o'clock which is the next day
		return $this->check_time(self::LATE_START, self::LATE_END, 1);
	}

	/**
	* Check if the shift is a night shift
	*
	* @return boolean
	*/
	public function is_night()
	{
		return $this->check_time(self::NIGHT_START, self::NIGHT_END);
	}

	/**
	 * Check if the shift is during a weekend
	 *
	 * @return boolean
	 */
	public function is_weekend()
	{
		return date('N', $this->middle()->getTimestamp()) >= 6;
	}

	/**
	 * Return end time of a shift
	 *
	 * @return DateTime
	 */
	public function end()
	{
		$date = new DateTime($this->start);
		$date->add(new DateInterval('PT' . $this->duration . 'H'));
		return $date;
	}

	/**
	 * Calculates the middle of a shift
	 *
	 * @return DateTime
	 */
	public function middle()
	{
		$date = new DateTime($this->start);
		$mid = $this->duration/2 * 60;  // use minutes instead of hours as decimals result in FatalError, even if ISO 8601 allows it...
		$date->add(new DateInterval('PT' . $mid . 'M'));
		return $date;
	}

	/**
	 * Calculate the rating for a shift:
	 * - If the shift is empty, 0 is returned
	 * - If the shift is for a single person only, the doubled rating of this user will be the rating
	 * - Otherwise the combined rating of the shift workers will be returned
	 *
	 * @return int
	 */
	public function rating()
	{
		if ($this->users->count() == 0)
			return 0;

		$rating = $this->users->sum('rating');
		if ($this->n_crew == 1 && $this->users->count() == 1)
			return $rating * 2;

		return $rating;
	}
}
