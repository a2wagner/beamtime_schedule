<?php

class Swap extends \Eloquent
{
	/**
	* Hashing algorithm which is used to create unique obfuscated links and check requests
	*
	* @var string
	*/
	protected static $algorithm = 'crc32';

	// Do not use timestamps for this model
	public $timestamps = false;

	protected $fillable = ['hash', 'user_id', 'original_shift_id', 'request_shift_id'];

	public static $rules = [
		'hash' => 'required|unique:swaps',
		'user_id' => 'required|integer',
		'original_shift_id' => 'required|integer',
		'request_shift_id' => 'required|integer',
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
	* The swap request belongs to a one or more users on the requested shift
	*
	* @return User
	*/
	public function request_users()
	{
		return $this->belongsToMany('User');
	}

	/**
	* Create the hash for the swap request
	*
	* @param int $user_id, int $shift_org, int $shift_req
	* @return string
	*/
	public static function create_hash($user_id, $shift_org, $shift_req)
	{
		return hash(self::$algorithm, $user_id . ':' . $shift_org . ',' . $shift_req);
	}

	/**
	* Validate the hash for the swap request
	*
	* @param string $hash, int $user_id, int $shift_org, int $shift_req
	* @return string
	*/
	public static function validate_hash($hash, $user_id, $shift_org, $shift_req)
	{
		return $hash === hash(self::$algorithm, $user_id . ':' . $shift_org . ',' . $shift_req);
	}

	/**
	* Check if the current swap is a shift request
	*
	* @return boolean
	*/
	public function is_request()
	{
		if ($this->original_shift_id !== $this->request_shift_id)
			return false;

		return $this->validate_hash($this->hash, $this->user_id, 0, $this->request_shift_id);
	}

	/**
	* Check if the current swap is a shift offer
	*
	* @return boolean
	*/
	public function is_offer()
	{
		if ($this->original_shift_id !== $this->request_shift_id)
			return false;

		return $this->validate_hash($this->hash, $this->user_id, $this->original_shift_id, 0);
	}
}
