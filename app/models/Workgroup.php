<?php

class Workgroup extends \Eloquent {
	protected $fillable = ['name', 'country'];

	// Do not use timestamps for this model
	public $timestamps = false;

	/**
	* A workgroup has many members (users); one to many relation
	*
	* @return Array of User models
	*/
	public function members()
	{
		return $this->hasMany('User');
	}
}
