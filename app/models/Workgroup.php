<?php

class Workgroup extends \Eloquent {
	protected $fillable = ['name', 'country', 'region'];

	// Do not use timestamps for this model
	public $timestamps = false;

	/**
	 * region int used for local group(s)
	 *
	 * @var int
	 */
	const LOCAL = 0;

	/**
	 * region int used for european groups
	 *
	 * @var int
	 */
	const EUROPE = 1;

	/**
	 * region int used for non-european groups
	 *
	 * @var int
	 */
	const WORLD = 2;

	/**
	* A workgroup has many members (users); one to many relation
	*
	* @return Array of User models
	*/
	public function members()
	{
		return $this->hasMany('User');
	}

	/**
	* Checks if a workgroup is local
	*
	* @return bool
	*/
	public function local()
	{
		return $this->region === self::LOCAL;
	}

	/**
	* Checks if a workgroup is european
	*
	* @return bool
	*/
	public function europe()
	{
		return $this->region === self::EUROPE;
	}

	/**
	* Checks if a workgroup is non-european
	*
	* @return bool
	*/
	public function world()
	{
		return $this->region === self::WORLD;
	}

	/**
	* Get string of the current workgroup region
	*
	* @return string
	*/
	public function region()
	{
		if ($this->local())
			return 'Local';
		if ($this->europe())
			return 'Europe';
		return 'Outside Europe';
	}

	/**
	* Get region string
	*
	* @return string
	*/
	public static function region_string($region)
	{
		if ($region === self::LOCAL)
			return 'Local';
		if ($region === self::EUROPE)
			return 'Europe';
		return 'Outside Europe';
	}
}
