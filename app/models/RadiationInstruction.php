<?php

class RadiationInstruction extends \Eloquent {

	protected $guarded = ['id'];

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
	* Get the user who renewed the radiation instruction
	* If no user is assigned, return NULL
	*
	* @return User
	*/
	public function renewedBy()
	{
		if (is_null($this->renewed_by))
			return NULL;

		return User::find($this->renewed_by);
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
