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

	public function type()
	{
		//TODO
		return 'day or night';
	}
}
