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
		if (Auth::guest())
			return Redirect::guest('login');

		// this will only work when the search string will be send as GET from the form because users.index is adressed as GET in the routes
		if (Input::has('search')) {
			$s = Input::get('search');
			$workgroups = Workgroup::where('name', 'LIKE', '%'.$s.'%')
				->orWhere('country', 'LIKE', '%'.$s.'%')
				->get()->lists('id');
			$users = $this->user->where('username', 'LIKE', '%'.$s.'%')
				->orWhere('first_name', 'LIKE', '%'.$s.'%')
				->orWhere('last_name', 'LIKE', '%'.$s.'%')
				->orWhereIn('workgroup_id', $workgroups);
			if (count($workgroups))
				$users = $users->orderBy('workgroup_id', 'asc')->paginate(50);
			else
				$users = $users->orderBy('last_name', 'asc')->paginate(20);
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
		$input = Input::all();

		if (!$this->user->fill($input)->isValid())
			return Redirect::back()->withInput()->withErrors($this->user->errors);

		$this->user->password = Hash::make(Input::get('password'));  //TODO try to guard password as it's not mass assigned here, just for security reasons...
		$this->user->save();

		// if this is the first user, we set him as an admin and enable him by default
		if ($this->user->id == 1) {
			// set the value manually because they're guarded, user->update(['isAdmin' => true]) won't work due to mass assignment protection (via array)
			$this->user->toggleAdmin();
			$this->user->enable();
			$this->user->save();
		}

		// added an enabled option, new users have first to get activated, return them to the homepage with an appropriate message
		return Redirect::to('')->with('success', 'Account created successfully. Please wait until your account gets activated by an Admin before you can login.');
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
		if (Auth::user()->isAdmin() || Auth::user()->id == $id) {
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
	 * Show an overview of all shifts a user has taken.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function shifts($id)
	{
		if (Auth::guest())
			return Redirect::guest('login');
		else if (Auth::user()->username !== $id) {
			$user = $this->user->whereUsername($id)->first();
			return View::make('users.show', ['user' => $user]);
		}

		$user = Auth::user();
		return View::make('users.shifts')->with('user', $user)->with('shifts', $user->shifts);
	}


	/**
	 * Renew Radiation Protection Instruction for the user.
	 *
	 * @param  int  $id
	 * @param  date $date
	 * @return Response
	 */
	public function renewRadiationInstruction($id)
	{
		if (Auth::guest())
			return Redirect::guest('login');

		$date = '';
		if (Input::has('date'))
			$date = Input::get('date');

		if (Auth::user()->isAdmin() || (Auth::user()->isRunCoordinator() && Auth::user()->hasRadiationInstruction($date))) {
			$rad = new RadiationInstruction;
			$rad->user_id = $id;
			$rad->begin = new DateTime($date);
			$rad->save();

			return Redirect::back()->with('success', 'Successfully extended Radiation Protection Instruction for ' . User::find($id)->get_full_name());
		} else
			return Redirect::back()->with('error', 'You are not allowed to extended the Radiation Protection Instruction');
	}


	/**
	 * Show a page of new registered users to enable them if logged-in user has admin privileges
	 *
	 * @return Response
	 */
	public function viewNew()
	{
		if (Auth::user()->isAdmin()) {
			$users = $this->user->where('role', '!&', User::ENABLED)->get();

			return View::make('users.enable', ['users' => $users]);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Show an overview page of all user-related actions the logged-in user can do if he has admin or PI privileges
	 *
	 * @return Response
	 */
	public function manageUsers()
	{
		if (Auth::user()->isAdmin() || Auth::user()->isPI()) {
			return View::make('users.manage');
		} else
			return Redirect::to('/users');
	}


	/**
	 * Show a page of all users where the admin flag can be toggled if logged-in user has admin privileges
	 *
	 * @return Response
	 */
	public function viewAdmins()
	{
		if (Auth::user()->isAdmin()) {
			// sort users first by the isAdmin attribute and afterwards alphabetically by their last name
			$users = $this->user->orderBy('role', 'desc')->orderBy('last_name', 'asc')->get();

			return View::make('users.admins', ['users' => $users]);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Show a page of all users where the run coordinator flag can be toggled if logged-in user has admin or PI privileges
	 *
	 * @return Response
	 */
	public function viewRunCoordinators()
	{
		if (Auth::user()->isAdmin() || Auth::user()->isPI()) {
			// sort users first by the isAdmin attribute and afterwards alphabetically by their last name
			$users = $this->user->orderBy('role', 'desc')->orderBy('last_name', 'asc')->get();

			return View::make('users.run_coordinators', ['users' => $users]);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Show a page of all users where the radiation expert flag can be toggled if logged-in user has admin privileges
	 *
	 * @return Response
	 */
	public function viewRadiationExperts()
	{
		if (Auth::user()->isAdmin()) {
			// sort users first by the isAdmin attribute and afterwards alphabetically by their last name
			$users = $this->user->orderBy('role', 'desc')->orderBy('last_name', 'asc')->get();

			return View::make('users.radiation_experts', ['users' => $users]);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Show a page of all users where the principle investigator flag can be toggled if logged-in user has admin or PI privileges
	 *
	 * @return Response
	 */
	public function viewPrincipleInvestigators()
	{
		if (Auth::user()->isAdmin() || Auth::user()->isPI()) {
			// sort users first by the isAdmin attribute and afterwards alphabetically by their last name
			$users = $this->user->orderBy('role', 'desc')->orderBy('last_name', 'asc')->get();

			return View::make('users.principle_investigators', ['users' => $users]);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Show a page with all users for which the currently logged in user is legitimated to renew the radiation protection instruction
	 *
	 * @return Response
	 */
	public function viewRadiationInstruction()
	{
		if (Auth::user()->isRadiationExpert() || (Auth::user()->isRunCoordinator() && Auth::user()->hasRadiationInstruction())) {
			if (Auth::user()->isRadiationExpert())
				$users = $this->user->get();
			else {
				$users = Auth::user()->rcshifts->reject(function($rcshift)  // get all run coordinator shifts for the logged in user
				{
					return new DateTime($rcshift->start) < new DateTime();  // reject all shifts in the past
				})
				->beamtime->unique()  // get the corresponding beamtimes
				->shifts->users->unique();  // get all users from these beamtimes
			}
			// sort the users by the date they got the last radiation instruction renewal
			$users->sortBy(function($user)
			{
				if ($user->radiation_instructions()->count())
					return strtotime($user->radiation_instructions()->orderBy('begin', 'desc')->first()->begin);  // convert date string to timestamp, otherwise the sorting is wrong sometimes
				else
					return 1;
			});

			return View::make('users.radiation')->with('users', $users);
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
		if (Auth::user()->isAdmin()) {
				$user = $this->user->find($id);
				$user->enable();
				$user->save();

				// send the enabled user a mail
				$subject = 'Account enabled';
				$msg = 'Hello ' . $user->first_name . ",\r\n\r\n";
				$msg.= 'your account has been enabled. You should be able to login and subscribe to shifts now. Please check your account information: ' . url() . '/users/' . $user->username . "/edit\r\n\r\n";
				$msg.= "A2 Beamtime Scheduler";
				$success = $user->mail($subject, $msg);

				return Redirect::route('users.new', ['users' => $this->user->where('role', '!&', User::ENABLED)->get()])->with('success', 'User ' . $user->username . ' enabled successfully');
		} else
			return Redirect::to('/users');
	}


	/**
	 * Toggle amin flag for user with the id $id
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function toggleAdmin($id)
	{
		if (Auth::user()->isAdmin()) {
				$user = $this->user->find($id);
				$user->toggleAdmin();
				$user->save();

				$msg = 'User ' . $user->first_name . ' ' . $user->last_name;
				if ($user->isAdmin())
					$msg .= ' is now an admin';
				else
					$msg .= ' is no longer an admin';

				return Redirect::route('users.admins', ['users' => $this->user->all()])->with('success', $msg);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Toggle run coordinator flag for user with the id $id
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function toggleRunCoordinator($id)
	{
		if (Auth::user()->isAdmin() || Auth::user()->isPI()) {
				$user = $this->user->find($id);
				$user->toggleRunCoordinator();
				$user->save();

				$msg = 'User ' . $user->first_name . ' ' . $user->last_name;
				if ($user->isRunCoordinator())
					$msg .= ' is now a run coordinator';
				else
					$msg .= ' is no longer a run coordinator';

				return Redirect::route('users.run_coordinators', ['users' => $this->user->all()])->with('success', $msg);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Toggle radiation expert flag for user with the id $id
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function toggleRadiationExpert($id)
	{
		if (Auth::user()->isAdmin()) {
				$user = $this->user->find($id);
				$user->toggleRadiationExpert();
				$user->save();

				$msg = 'User ' . $user->get_full_name();
				if ($user->isRadiationExpert())
					$msg .= ' is now a radiation expert';
				else
					$msg .= ' is no longer a radiation expert';

				return Redirect::route('users.radiation_experts', ['users' => $this->user->all()])->with('success', $msg);
		} else
			return Redirect::to('/users');
	}


	/**
	 * Toggle principle investigator flag for user with the id $id
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function togglePrincipleInvestigator($id)
	{
		if (Auth::user()->isAdmin() || Auth::user()->isPI()) {
				$user = $this->user->find($id);
				$user->togglePI();
				$user->save();

				$msg = 'User ' . $user->first_name . ' ' . $user->last_name;
				if ($user->isPI())
					$msg .= ' is now a principle investigator';
				else
					$msg .= ' is no longer a principle investigator';

				return Redirect::route('users.principle_investigators', ['users' => $this->user->all()])->with('success', $msg);
		} else
			return Redirect::to('/users');
	}


}
