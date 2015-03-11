<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	/* PHP can't parse non-trivial expressions in initializers. For this reason it isn't possible to do someting like array_only(...) in a public static $something definition. To circumvent this the variables which will hold arrays for special rule sets will only be defined and then parsed in the __construct() method. The parent __construct() method has to be called for correct functionality. */
	public function __construct($attributes = array(), $exists = false)
	{
		parent::__construct($attributes, $exists);  // Initialize the model according to the parent class functionality

		// change the rules according to editing users where we don't need the username or any password confirmation
		self::$rules_edit = array_except(static::$rules, array('username', 'password', 'password_confirmation'));
		// keep only the password related fields and add an entry which will be used to check if the old password is correct
		self::$rules_pwChange = array_add(array_only(static::$rules, array('password', 'password_confirmation')), 'password_old', 'required');
	}

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

	public static $rules_edit;
	public static $rules_pwChange;

	public $errors;

	// different roles a user can have, stored in an unsigned 8bit integer (max value 255_10 = 11111111_2)
	const ENABLED = 1;
	const RUN_COORDINATOR = 2;
	const ADMIN = 4;
	const AUTHOR = 8;

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

	/**
	 * Get the full name of a user, firstname + lastname
	 *
	 * @return string
	 */
	public function get_full_name()
	{
		return $this->first_name . ' ' . $this->last_name;
	}

	/**
	 * Send a mail to the current user
	 *
	 * @param string $subject
	 * @param string $message
	 * @param array $cc
	 * @return boolean
	 */
	public function mail($subject, $msg, $cc = null)
	{
		$mail = new Sendmail();

		return $mail->send_single($this->email, $subject, $msg, $cc);
	}

	/**
	 * Check if the current user is enabled
	 *
	 * @return boolean
	 */
	public function is_enabled()
	{
		return $this->role & $enabled;
	}

	/**
	 * Check if the current user is a run coordinator
	 *
	 * @return boolean
	 */
	public function is_run_coordinator()
	{
		return $this->role & $run_coordinator;
	}

	/**
	 * Check if the current user is an admin
	 *
	 * @return boolean
	 */
	public function is_admin()
	{
		return $this->role & $admin;
	}

	/**
	 * Check if the current user is an author
	 *
	 * @return boolean
	 */
	public function is_author()
	{
		return $this->role & $author;
	}

}
