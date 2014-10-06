<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	//protected $fillable = ['first_name', 'last_name', 'user_name', 'email', 'password', 'rating'];
	// fillable leads to problems with forms because not listed variables can't be filled via forms (mass assignment security); use black list instead white list
	protected $guarded = ['id', 'isAdmin', 'enabled'];

	public static $rules = [
		'first_name' => 'required',
		'last_name' => 'required',
		'username' => 'required|unique:users',
		'email' => 'required|min:7|email|unique:users,email',
		'workgroup_id' => 'required',
		'password' => 'required|min:6|confirmed',
		'password_confirmation' => 'required|same:password',
		'rating' => 'required'
	];

	public $errors;

	use UserTrait, RemindableTrait;

	// create custom validation messages ------------------
	protected static $messages = array(
		//'required' => 'The :attribute is really really really important.',
		'same' 	=> 'The :others must match.'
	);

	/**
	* One to many relation between user and workgroup; a user belongs to a workgroup
	*
	* @return Workgroup
	*/
	public function workgroup()
	{
		return $this->belongsTo('Workgroup');
	}

	/**
	* Many to many relation, a user can take several shifts
	*
	* @return Array of Shift objects
	*/
	public function shifts()
	{
		return $this->belongsToMany('Shift');
	}

	/**
	* A user can be run coordinator of several beamtimes
	*
	* @return Array of Beamtime objects
	*/
	public function beamtimes()
	{
		return $this->belongsToMany('Beamtime', 'run_coordinators');
	}

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

	/**
	* Validation of the filled attributes concerning the defined rules and messages for the check
	*
	* @return bool of validation check
	*/
	public function isValid()
	{
		$validation = Validator::make($this->attributes, static::$rules, static::$messages);

		// remove password_confirmation as we don't want to store it in the database
		$this->attributes = array_except($this->attributes, 'password_confirmation');

		if ($validation->passes())
			return true;

		$this->errors = $validation->messages();

		return false;
	}

}
