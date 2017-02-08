<?php

use \Exception;

class SessionsController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if (Auth::check())
			return Redirect::to('')->with('success', 'You are already logged in');
		return View::make('sessions.login');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		// Get all the necessary inputs
		// id is used for login, username is used for validation to return correct error-strings
		$userdata = Input::only('username', 'password');

		// Declare the rules for the form validation
		$rules = array(
			'username'  => 'required',
			'password'  => 'required'
		);

		// Validate the inputs
		$validator = Validator::make($userdata, $rules);

		// Check if the form validates with success
		if (!$validator->passes())
			return Redirect::to('login')->withErrors($validator)->withInput(Input::except('password'));  // Something went wrong

		// All fields are filled, check if we can connect to the LDAP server now
		$ld = new LDAP();  // Create an instance of the LDAP helper class
		$ldap = $ld->test_connection();

		// First check if the username exists
		$user_localDB = false;
		if (User::whereUsername($userdata['username'])->count())
			$user_localDB = true;
		else if (!$ldap)
			return Redirect::back()->withErrors(array('username' => 'Username does not exist!'))->withInput(Input::except('password'))
					->with('warning', 'The KPH LDAP server is not available and your username does not exist. You may want to register for an account.');
		$user_LDAP = false;
		if ($ldap)
			if ($ld->user_exists($userdata['username']))
				$user_LDAP = true;

		// Check if the user got enabled after registration, redirect him to home with an error otherwise
		if ($user_localDB && !User::whereUsername(Input::get('username'))->first()->isEnabled())
			return Redirect::to('')->with('error', 'You\'re not enabled yet. Please wait until your account gets activated.');

		// At this point we have only valid usernames, either on the LDAP server or in the local database
		// Now try to authenticate the user
		if ($user_LDAP) {
			if ($ld->authenticate($userdata['username'], $userdata['password'])) {
				$user = User::whereUsername($userdata['username'])->first();
				// If the user doesn't exist in the local database, it might be his first login
				// Copy all necessary information from the LDAP server to the local database and redirect him to the profile edit page
				if (!$user_localDB) {
					$data = $ld->search_user($userdata['username']);
					if (!is_array($data))
						throw new Exception("Something went wrong, unexpected LDAP query result...");
					$user = new User;
					$user->username = $userdata['username'];
					$user->password = 'ldap';
					$user->first_name = $data['givenname'][0];
					$user->last_name = $data['sn'][0];
					$user->email = $data['mail'][0];
					if (in_array('telephonenumber', $data))
						$user->phone_institute = $data['telephonenumber'][0];
					if (in_array('homephone', $data))
						$user->phone_private = $data['homephone'][0];
					$user->workgroup_id = 1;
					$user->rating = 1;
					$user->ldap_id = $data['uidnumber'][0];
					$user->enable();
					$user->last_login = new DateTime();
					$user->save();

					Auth::login($user);  // authenticate user

					// if this is the first user, we set him as an admin and enable him by default
					if ($user->id == 1) {
						$user->toggleAdmin();
						$user->save();

						return Redirect::to('/users/' . $userdata['username'] . '/edit')
								->with('success', 'Account created successfully. Set as Admin by default as it\'s the first user account. Check the information below and update them if needed.');
					}

					return Redirect::to('/users/' . $userdata['username'] . '/edit')
							->with('info', 'You\'ve logged in for the first time. Check the information below and update them if needed.');
				}
				// Authenticate and redirect the user to the intended page, otherwise as the default to home; save last login timestamp
				Auth::login($user);
				$user->store_login();
				return Redirect::intended('')->with('success', 'You have logged in successfully');
			} elseif (User::whereUsername($userdata['username'])->first()->password !== 'ldap') {  // if the user has a LDAP account, but a different password
				if (Auth::attempt($userdata)) {
					// save last login timestamp
					Auth::user()->store_login();
					// Redirect the user to the intended page, otherwise as the default to home
					return Redirect::intended('')->with('success', 'You have logged in successfully');
				} else {
					// Redirect to the login page
					return Redirect::back()->withErrors(array('password' => 'Password invalid'))->withInput(Input::except('password'));
				}
			} else {
				// Redirect to the login page
				return Redirect::back()->withErrors(array('password' => 'Password invalid'))->withInput(Input::except('password'));
			}
		} else {
			if (Auth::attempt($userdata)) {
				// save last login timestamp
				Auth::user()->store_login();
				// Redirect the user to the intended page, otherwise as the default to home
				return Redirect::intended('')->with('success', 'You have logged in successfully');
			} else {
				// Redirect to the login page
				return Redirect::back()->withErrors(array('password' => 'Password invalid'))->withInput(Input::except('password'));
			}
		}
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		Auth::logout();

		return Redirect::to('')->with('success', 'You are logged out');
	}


}
