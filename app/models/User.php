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
		// only the username and the corresponding id is needed to change the user account to a KPH account
		self::$rulesKPH = array_add(array_only(static::$rules, array('username')), 'user_id', 'required|integer');
	}

	//protected $fillable = ['first_name', 'last_name', 'user_name', 'email', 'password', 'rating'];
	// fillable leads to problems with forms because not listed variables can't be filled via forms (mass assignment security); use black list instead white list
	protected $guarded = ['id', 'role', 'last_login','retire_date'];

	public static $rules = [
		'first_name' => 'required',
		'last_name' => 'required',
		'username' => 'required|max:20|unique:users',
		'email' => 'required|min:7|email|unique:users,email',
		'workgroup_id' => 'required',
		'password' => 'required|min:6|confirmed',
		'password_confirmation' => 'required|same:password',
		'rating' => 'required',
		'role' => 'integer|between:0,255',
		'last_login' => 'date',
		'start_date' => 'date',
		'retire_date' => 'date'
	];

	public static $rules_edit;
	public static $rules_pwChange;
	public static $rulesKPH;

	public $errors;

	// different roles a user can have, stored in an unsigned 8bit integer (max value 255_10 = 11111111_2)
	const ENABLED = 1;
	const RETIRED = 2;
//	const SOMETHING = 4;
	const RUN_COORDINATOR = 8;
	const AUTHOR = 16;
	const RADIATION_EXPERT = 32;
	const PI = 64;
	const ADMIN = 128;  // use the highest bit for admins

	// the amount of days since the last login from where a user is considered inactive
	const INACTIVE_DAYS = 180;

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
	 * Many to many relation, a user can take several run coordinator shifts
	 *
	 * @return Array of RCShift objects
	 */
	public function rcshifts()
	{
		return $this->belongsToMany('RCShift', 'rc_shift_user', 'user_id', 'rc_shift_id');
	}

	/**
	* A user can subscribe to several beamtimes
	*
	* @return Array of Beamtime objects
	*/
	public function beamtimes()
	{
		return $this->shifts->beamtime->unique();
	}

	/**
	* Many to many relation, a user has multiple radiation instructions
	*
	* @return Array of RadiationInstruction objects
	*/
	public function radiation_instructions()
	{
		return $this->hasMany('RadiationInstruction');
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
	 * Use the custom collection that allows tapping
	 *
	 * @return UtilityCollection
	 */
	public function newCollection(array $models = array())
	{
		return new UtilityCollection($models);
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
	 * @param User   $from
	 * @param array  $cc
	 * @return boolean
	 */
	public function mail($subject, $msg, $from = null, $cc = null)
	{
		$mail = new Sendmail();

		return $mail->send_single($this->email, $subject, $msg, $from, $cc);
	}

	/**
	 * Check if a user has a valid radiation protection instruction for a given date
	 *
	 * @param string $date
	 * @return boolean
	 */
	public function hasRadiationInstruction($date = '')
	{
		$hasInstruction = false;
		$date = new DateTime($date);
		$this->radiation_instructions()->get()->each(function($instruction) use($date, &$hasInstruction)
		{
			$start = new DateTime($instruction->begin);
			if (!$start->diff($date)->format("%y") && $date > $start) {
				$hasInstruction = true;
				return;
			}
		});
		return $hasInstruction;
	}

	/**
	 * Store the last login timestamp
	 *
	 * @return void
	 */
	public function store_login()
	{
		$this->timestamps = false;
		$this->last_login = new DateTime();
		$this->save();
	}

	/**
	 * Returns the amount of days since the last login of a user
	 *
	 * @return int $days
	 */
	public function last_active()
	{
		$now = new DateTime();
		$last_login = new DateTime($this->last_login);
		$diff = $now->diff($last_login);

		return $diff->days;
	}

	/**
	 * Returns the amount of months since the last login of a user
	 *
	 * @return int $months
	 */
	public function last_active_months()
	{
		$now = new DateTime();
		$last_login = new DateTime($this->last_login);
		$diff = $now->diff($last_login);

		return $diff->m + $diff->y*12;
	}

	/**
	 * Returns if the user account is active or inactive
	 * inactive means the last login took place more than INACTIVE_DAYS ago
	 *
	 * @return boolean
	 */
	public function is_active()
	{
		return $this->last_active() < self::INACTIVE_DAYS;
	}

	
	
	/**
	 * Returns if the user account was active or inactive
	 * during a specific year
	 * @param Year $year
	 * @return boolean
	 */
	public function was_active($year)
	{
		//echo $year;
		echo "retire : $this->retire_date \n\n";

		$date = new DateTime($this->retire_date);
		echo "date   : $this->retire_date \n";
	
		dd($date->format("Y"));



		return true;
	//	return $year < $this->retire_date->format("Y");
		
	}

	/**
	 * Returns the amount of days since the user took the last shift
	 * Value is negative in case of no shifts
	 *
	 * @return int $days
	 */
	public function last_shift()
	{
		$now = new DateTime();
		$last_shift = $this->shifts->orderBy('start', 'desc')->first();

		if (!$last_shift)
			return -1;

		$last = new DateTime($last_shift->start);
		$diff = $now->diff($last);

		return $diff->days;
	}

	/**
	 * Returns the amount of months since the user took the last shift
	 * Value is negative in case of no shifts
	 *
	 * @return int $months
	 */
	public function last_shift_months()
	{
		$now = new DateTime();
		$last_shift = $this->shifts->orderBy('start', 'desc')->first();

		if (!$last_shift)
			return -1;

		$last = new DateTime($last_shift->start);
		$diff = $now->diff($last);

		return $diff->m + $diff->y*12;
	}

	/**
	 * Counts the amount of shifts taken before a certain shift
	 * The reference time point is taken from the given Shift parameter
	 *
	 * @param Shift $shift
	 * @return int
	 */
	public function experience($shift)
	{
		// check if reasonable parameter has been passed
		if (is_int($shift) || $shift instanceof Shift) {
			if (is_int($shift))
				$shift = Shift::find($shift);
			if (!$shift->start) {
				echo "Error: Could not retrieve start time from given Shift object";
				dd($shift);
			}
		} else {
			echo "Error: Wrong object passed to method";
			dd($shift);
		}

		$time = strtotime($shift->start);
		// determine amount of shifts taken before the given shift starts
		$amount = $this->shifts->filter(function($shift) use($time)
		{
			return strtotime($shift->start) < $time;
		})->count();

		return $amount;
	}

	/**
	 * Checks if a user is experienced based on the amount of shifts taken before a certain shift
	 * The reference time point is taken from the given Shift parameter
	 *
	 * @param Shift $shift
	 * @return boolean
	 */
	public function experienced($shift)
	{
		// check if reasonable parameter has been passed
		if (is_int($shift) || $shift instanceof Shift) {
			if (is_int($shift))
				$shift = Shift::find($shift);
			if (!$shift->start) {
				echo "Error: Could not retrieve start time from given Shift object";
				dd($shift);
			}
		} else {
			echo "Error: Wrong object passed to method";
			dd($shift);
		}

		return $this->experience($shift) >= Shift::EXPERIENCE_BLOCK;
	}

	/**
	 * Checks if a user is experienced based on the amount of total shifts taken
	 *
	 * @return boolean
	 */
	public function is_experienced()
	{
		return $this->shifts->count() >= Shift::EXPERIENCE_BLOCK;
	}

	/**
	 * Get all different roles of a user
	 *
	 * @return array $roles
	 */
	public function get_roles()
	{
		$roles = array();

		if (!$this->isEnabled())
			array_push($roles, 'Not enabled');
		if (!$this->isRetired())
			array_push($roles, 'Retired');
		if ($this->isAdmin())
			array_push($roles, 'Admin');
		if ($this->isPI())
			array_push($roles, 'PI');
		if ($this->isRadiationExpert())
			array_push($roles, 'Radiation Expert');
		if ($this->isRunCoordinator())
			array_push($roles, 'Run Coordinator');

		return $roles;
	}

	/**
	 * Get all different roles of a user
	 *
	 * @return string $roles
	 */
	public function get_roles_string()
	{
		$roles = implode(', ', $this->get_roles());

		return $roles;
	}

	/**
	 * Check if the current user is enabled
	 *
	 * @return boolean
	 */
	public function isEnabled()
	{
		return $this->isFlagSet(self::ENABLED);
	}

	/**
	 * Enable a new user
	 *
	 * @return void
	 */
	public function enable()
	{
		$this->role |= self::ENABLED;
	}

	/**
	 * Check if the current user is a run coordinator
	 *
	 * @return boolean
	 */
	public function isRunCoordinator()
	{
		return $this->isFlagSet(self::RUN_COORDINATOR);
	}

	/**
	 * Set the current user as a run coordinator
	 *
	 * @return void
	 */
	public function setRunCoordinator()
	{
		$this->role |= self::RUN_COORDINATOR;
	}

	/**
	 * Toggle the run coordinator flag of a user role
	 *
	 * @return void
	 */
	public function toggleRunCoordinator()
	{
		$this->role ^= self::RUN_COORDINATOR;
	}

	/**
	 * Check if the current user is a radiation expert
	 *
	 * @return boolean
	 */
	public function isRadiationExpert()
	{
		return $this->isFlagSet(self::RADIATION_EXPERT);
	}

	/**
	 * Set the current user as a radiation expert
	 *
	 * @return void
	 */
	public function setRadiationExpert()
	{
		$this->role |= self::RADIATION_EXPERT;
	}

	/**
	 * Toggle the radiation expert flag of a user role
	 *
	 * @return void
	 */
	public function toggleRadiationExpert()
	{
		$this->role ^= self::RADIATION_EXPERT;
	}

	/**
	 * Check if the current user is a principle investigator
	 *
	 * @return boolean
	 */
	public function isPI()
	{
		return $this->isFlagSet(self::PI);
	}

	/**
	 * Set the current user as a principle investigator
	 *
	 * @return void
	 */
	public function setPI()
	{
		$this->role |= self::PI;
	}

	/**
	 * Toggle the principle investigator flag of a user role
	 *
	 * @return void
	 */
	public function togglePI()
	{
		$this->role ^= self::PI;
	}

	/**
	 * Check if the current user is retired
	 *
	 * @return boolean
	 */
	public function isRetired()
	{
		return $this->isFlagSet(self::RETIRED);
	}

	/**
	 * Mark the current user as retired
	 *
	 * @return void
	 */
	public function setRetired()
	{
		$this->role |= self::RETIRED;
	}

	/**
	 * Toggle the retired flag of a user role
	 *
	 * @return void
	 */
	public function toggleRetired()
	{
		$this->role ^= self::RETIRED;
	}

	/**
	 * Check if the current user is an admin
	 *
	 * @return boolean
	 */
	public function isAdmin()
	{
		return $this->isFlagSet(self::ADMIN);
	}

	/**
	 * Toggle the admin flag of a user role
	 *
	 * @return void
	 */
	public function toggleAdmin()
	{
		$this->role ^= self::ADMIN;
	}

	/**
	 * Check if the current user is an author
	 *
	 * @return boolean
	 */
	public function isAuthor()
	{
		return $this->isFlagSet(self::AUTHOR);
	}

	/**
	 * Set the current user as an author
	 *
	 * @return void
	 */
	public function setAuthor()
	{
		$this->role |= self::AUTHOR;
	}

	/**
	 * Toggle the author flag of a user role
	 *
	 * @return void
	 */
	public function toggleAuthor()
	{
		$this->toggleFlag(self::AUTHOR);
	}


	/* General methods to get and manipulate bits of $this->role */

	/**
	 * Check if a specific bit is set
	 *
	 * @param int $flag
	 * @return boolean
	 */
	protected function isFlagSet($flag)
	{
		return (($this->role & $flag) == $flag);
	}

	/**
	 * Set a specific bit to the given value
	 *
	 * @param int $flag, bool $value
	 * @return void
	 */
	protected function setFlag($flag, $value)
	{
		if ($value) {
			$this->role |= $flag;
		} else {
			$this->role &= ~$flag;
		}
	}

	/**
	 * Flip a specific bit
	 *
	 * @param int $flag
	 * @return void
	 */
	protected function toggleFlag($flag)
	{
		$this->role ^= $flag;
	}

}
