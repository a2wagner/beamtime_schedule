<?php

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
			//return Redirect::to('')->with('success', 'You are already logged in');
			return Redirect::to('/admin');
		return View::make('sessions.login');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		// First check if the username exists
		if (!User::whereUsername(Input::get('username'))->count())
			return Redirect::back()->withErrors(array('username' => 'Username does not exist!'))->withInput(Input::except('password'));

		// Check if the user got enabled after registration, redirect him to home with an error otherwise
		if (!User::whereUsername(Input::get('username'))->first()->enabled)
                return Redirect::to('')->with('error', 'You\'re not enabled yet. Please wait until your account gets activated.');

        // Get all the inputs
        // id is used for login, username is used for validation to return correct error-strings
        $userdata = Input::only('username', 'password');

        // Declare the rules for the form validation.
        $rules = array(
            'username'  => 'required',
            'password'  => 'required'
        );

        // Validate the inputs.
        $validator = Validator::make($userdata, $rules);

        // Check if the form validates with success.
        if ($validator->passes())
        {
            // Try to log the user in.
            if (Auth::attempt($userdata))
            {
                // Redirect the user to the intended page, otherwise as the default to home
                return Redirect::intended('')->with('success', 'You have logged in successfully');
            }
            else
            {
                // Redirect to the login page.
                return Redirect::back()->withErrors(array('password' => 'Password invalid'))->withInput(Input::except('password'));
            }
        }

        // Something went wrong.
        return Redirect::to('login')->withErrors($validator)->withInput(Input::except('password'));
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

		//return Redirect::route('sessions.create');
		return Redirect::to('')->with('success', 'You are logged out');
	}


}
