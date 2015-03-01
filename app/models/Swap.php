<?php

class Swap extends \Eloquent {
	protected $fillable = ['hash', 'user_id', 'original_shift_id', 'request_shift_id', 'request_user_id'];

	// Do not use timestamps for this model
	public $timestamps = false;

	public static $rules = [
		'hash' => 'required|unique:swaps',
		'user_id' => 'required|integer',
		'original_shift_id' => 'required|integer',
		'request_shift_id' => 'required|integer',
		'request_user_id' => 'integer',
	];

	/**
	* A swap request belongs to one user
	*
	* @return User
	*/
	public function user()
	{
		return $this->belongsTo('User');
	}

	/**
	* A swap request belongs to one original shift the user subscribed to
	*
	* @return Shift
	*/
	public function original_shift()
	{
		return $this->belongsTo('Shift', 'original_shift_id');
	}

	/**
	* A swap request belongs to one requested shift the user want to change to
	*
	* @return Shift
	*/
	public function request_shift()
	{
		return $this->belongsTo('Shift', 'request_shift_id');
	}

	/**
	* Optional: The swap request belongs to a specific user on the requested shift
	*
	* @return User
	*/
	public function request_user()
	{
		return $this->belongsTo('User', 'request_user_id');
	}

	/**
	* Create the CRC32 hash for the swap request
	*
	* @param int $beamtime, int $shift_org, int $shift_req
	* @return string
	*/
	public function create_hash($beamtime, $shift_org, $shift_req)
	{
		return hash('crc32', $beamtime . ':' . $shift_org . ',' . $shift_req);
	}

	/**
	* Validate the CRC32 hash for the swap request
	*
	* @param string $hash, int $beamtime, int $shift_org, int $shift_req
	* @return string
	*/
	public function validate_hash($hash, $beamtime, $shift_org, $shift_req)
	{
		return $hash === hash('crc32', $beamtime . ':' . $shift_org . ',' . $shift_req);
	}
}
