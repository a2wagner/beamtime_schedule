<?php

class UsersController extends \BaseController {

	protected $user;

	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//return User::orderBy('username', 'asc')->get();
		//return User::orderBy('username', 'asc')->take(2)->get();  // only the first two that match

		//if (!Auth::check())
		//	return Redirect::to('/login');
		// the below method is better, because after login one gets redirected to the inteded page
		if (Auth::guest())
			return Redirect::guest('login');

		// this will only work when the search string will be send as GET from the form because users.index is adressed as GET in the routes
		//TODO: check to append search query etc. to pagination query and try if it works - http://laravel.com/docs/pagination#appending-to-pagination-links
		if (Input::has('search')) {
			$s = Input::get('search');
			$users = $this->user->where('username', 'LIKE', '%'.$s.'%')
				->orWhere('first_name', 'LIKE', '%'.$s.'%')
				->orWhere('last_name', 'LIKE', '%'.$s.'%')
				->orderBy('last_name', 'asc')->paginate(20);
			return View::make('users.index', ['users' => $users]);
		}

		//$users = $this->user->all();
		// use pagination instead
		if (Input::has('sort'))
			$users = $this->user->orderBy('username', Input::get('sort'))->paginate(20);
		else
			$users = $this->user->paginate(20);

		return View::make('users.index', ['users' => $users])->withInput(Input::all());
	}

	//TODO delete? tried to combine sort and search, but it didn't work. added now an if statement to show only the sorting links when no search was done.
	public function index_test(){
		//return User::orderBy('username', 'asc')->get();
		//return User::orderBy('username', 'asc')->take(2)->get();  // only the first two that match

		if (Auth::guest())
			return Redirect::guest('login');

		$query = NULL;
		$sort = NULL;

		if (Input::has('sort')) {
			$sort = Input::get('sort');
			$query = array_add($query, 'sort', $sort);
		} else
			$sort = 'asc';

		// this will only work when the search string will be send as GET from the form because users.index is adressed as GET in the routes
		if (Input::has('search')) {
			$s = Input::get('search');
			$query = array_add($query, 'search', $s);
			$users = $this->user->where('username', 'LIKE', '%'.$s.'%')
				->orWhere('first_name', 'LIKE', '%'.$s.'%')
				->orWhere('last_name', 'LIKE', '%'.$s.'%')
				->orderBy('last_name', $sort)->paginate(20);
			return View::make('users.index', ['users' => $users]);
		} else
			$users = $this->user->paginate(20);

		//$users = $this->user->all();
		// use pagination instead
		/*if (Input::has('sort'))
			$users = $this->user->orderBy('username', Input::get('sort'))->paginate(20);
		else
			$users = $this->user->paginate(20);*/

		if ($query) {
			$queryString = http_build_query($query);
			URL::to('users?' . $queryString);
		}
		return View::make('users.index', ['users' => $users]);
}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		// a logged in user don't need to create an account, redirect to edit profile page
		if (Auth::check())
			return Redirect::to('users/'.Auth::user()->username.'/edit');

		$workgroups = array('' => 'Please select your workgroup') + Workgroup::orderBy('name', 'asc')->lists('name', 'id');

		return View::make('users.create', ['workgroups' => $workgroups]);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		/*$validation = Validator::make(Input::all(), ['username' => 'required', 'password' => 'required']);

		if ($validation->fails())
			return Redirect::back()->withInput()->withErrors($validation->messages());*/

		/*if (!$this->user->isValid($input = Input::all()))
			return Redirect::back()->withInput()->withErrors($this->user->errors());*/

		$input = Input::all();

		if (!$this->user->fill($input)->isValid())
			return Redirect::back()->withInput()->withErrors($this->user->errors);

		/*$user = new User;
		$user->username = Input::get('username');
		$user->password = Hash::make(Input::get('password'));
		$user->save();*/
		//$this->user->create($input);
		$this->user->password = Hash::make(Input::get('password'));  //TODO try to guard password as it's not mass assigned here, just for security reasons...
		$this->user->save();

		// if this is the first user, we set him as an admin and enable him by default
		if ($this->user->id == 1) {
			// set the value manually because they're guarded, user->update(['isAdmin' => true]) won't work due to mass assignment protection (via array)
			$this->user->isAdmin = true;
			$this->user->enabled = true;
			$this->user->save();
		}

		//return Redirect::route('users.show', ['user' => $this->user->username]);
		// alternative: login the user directly after account creation
		//if (Auth::attempt(Input::only('username', 'password')))
			//return Redirect::to('')->with('success', 'Account created successfully');
			// redirect the user to his account view after it was created
			//return Redirect::to('users/'.$this->user->username)->with('success', 'Account created successfully');
			// added an enabled option, new users have first get activated, return them to the homepage with an appropriate message
			return Redirect::to('')->with('success', 'Account created successfully. Please wait until your account gets activated by an Admin before you can login.');
		//else
		//	return Redirect::to('login')->withErrors(array('password' => 'Password invalid'))->withInput(Input::except('password'));
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if (Auth::guest())
			return Redirect::guest('login');

		$user = $this->user->whereUsername($id)->first();

		return View::make('users.show', ['user' => $user]);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if (Auth::guest())
			return Redirect::guest('login');

		$user = $this->user->whereUsername($id)->first();//User::find($id);

		$workgroups = Workgroup::orderBy('name', 'asc')->lists('name', 'id');//Workgroup::lists('name', 'id');

		return View::make('users.edit')->with('user', $user)->with('workgroups', $workgroups);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		if (Auth::guest())
			return Redirect::guest('login');

		// allow only admin or the current user to edit the user information
		if (Auth::user()->isAdmin || Auth::user()->id == $id) {
			$user = $this->user->whereId($id)->first();
			$data = array();
			$validator = NULL;
			/* Use the PATCH HTTP request for editing profile information and the PUT method for changing the password */
			if (Input::get('_method') === "PATCH") {
				// copy only field values to the data array which are allowed to be changed
				$data = array_only(Input::all(), array('first_name', 'last_name', 'email', 'workgroup_id', 'phone_institute', 'phone_private', 'phone_mobile', 'rating'));
				// get the defined rules for this action
				$rules = User::$rules_edit;
				// change the email rule because we sometimes want to "change" our email to the value which already exists in the database -> force excluding this by adding the id
				$rules['email'] = 'required|min:7|email|unique:users,email,'.$id;
				$validator = Validator::make($data, $rules);
				if ($validator->fails()){
					return Redirect::back()->withInput()->withErrors($validator);}
				$user->fill($data)->save();
				return Redirect::back()->with('success', 'Profile data edited successfully');
			} else if (Input::get('_method') === "PUT") {
				$data = array_only(Input::all(), array('password_old', 'password', 'password_confirmation'));
				$rules = User::$rules_pwChange;
				// check if the old password matches the current user's password hash
				if (!Hash::check($data['password_old'], $user->password))
					return Redirect::back()->with('error', 'Wrong password!');
				$validator = Validator::make($data, $rules);
				if ($validator->fails()){
					return Redirect::back()->withErrors($validator);}
				// if everything is fine, hash the new password
				$user->password = Hash::make($data['password']);
				$user->save();
				return Redirect::back()->with('success', 'Password changed successfully');
			} else
				return "Error 404, wrong HTTP request!";
		}

		return Redirect::back()->with('error', 'You are not allowed to edit this user');

		//$user = $this->user->whereUsername($id)->first();
		//$user->property = 'new value';
		//$user->save();

		// http://stackoverflow.com/questions/22686817/laravel-model-update-only-one-field
		// Something::find($id)->fill($with_data)->save();
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if (Auth::guest())
			return Redirect::guest('login');

		$user = User::find($id);
		// store the username for the success message
		$name = $user->username;
		$user->delete();

		// redirect to the users overview
		return Redirect::to('/users')->with('success', 'User ' . $name . ' deleted successfully');
	}


	/**
	 * Show a page of new registered users to enable them if logged-in user has admin privileges
	 *
	 * @return Response
	 */
	public function view()
	{
		if (Auth::user()->isAdmin) {
			$users = $this->user->where('enabled', '=', 0)->get();

			return View::make('users.enable', ['users' => $users]);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Enable the user with specific id $id
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function enable($id)
	{
		if (Auth::user()->isAdmin) {
				$user = $this->user->find($id);
				$user->enabled = true;
				$user->save();

				return Redirect::route('users.new', ['users' => $this->user->where('enabled', '=', 0)->get()])->with('success', 'User ' . $user->username . ' enabled successfully');
		} else
			return Redirect::to('/users');
	}


}
