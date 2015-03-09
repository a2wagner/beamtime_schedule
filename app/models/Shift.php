<?php

class Shift extends \Eloquent {
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
}
