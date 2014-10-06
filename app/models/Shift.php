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
}
