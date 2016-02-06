<?php

class RadiationInstruction extends \Eloquent {

	protected $guarded = ['id'];

	// Do not use timestamps for this model
	public $timestamps = false;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'radiation_instructions';

	/**
	* One to many relation between user and radiation_instruction; a user has multiple radiation instructions
	*
	* @return User
	*/
	public function user()
	{
		return $this->belongsTo('User');
	}

	/**
	 * Return the date when the Radiation Protection Instruction expires
	 *
	 * @return end date
	 */
	public function end()
	{
		return date('Y-m-d', strtotime('+1 year', strtotime($this->begin)));
	}
}
