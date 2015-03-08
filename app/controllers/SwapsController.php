<?php

class SwapsController extends \BaseController {

	protected $swap;

	public function __construct(Swap $swap)
	{
		$this->swap = $swap;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//TODO: maybe show a list of all existing swap requests?
	}


	/**
	 * Display the beamtime with swap option.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function create($id)
	{
		$shift = Shift::find($id);
		$beamtime_id = $shift->beamtime->id;
		if ($beamtime = Beamtime::find($beamtime_id))
			$shifts = $beamtime->shifts;
		else
			return 'Beamtime not found!';

		return View::make('beamtimes.swap')->with('beamtime', $beamtime)->with('shifts', $shifts)->with('current', $id);
	}


	/**
	 * Create the swap request by storing an entry in the database sending emails to the users of this shift
	 *
	 * @param int $shift_org_id, int $shift_req_id
	 * @return Response
	 */
	public function store($org, $req)
	{
		$shift_org = Shift::find($org);
		$shift_request = Shift::find($req);
		$beamtime_id = $shift_request->beamtime->id;
		if ($beamtime_id != $shift_org->beamtime->id)
			return Redirect::back()->with('error', 'The shifts don\'t belong to the same beamtime, please use the interface corretly!');
		if (!$shift_org->users->find(Auth::id())->count())
			return Redirect::back()->with('error', 'You\'re not subscribed to the original shift, please use the interface corretly!');

		// the query seems reasonable, create an entry in the database for this request and send emails to the users of this shift
		$swap = new Swap();
		$hash = $swap->create_hash(Auth::id(), $org, $req);

		// check if this request is already in the database
		if (Swap::whereHash($hash)->count())
			return Redirect::to('beamtimes/' . $beamtime_id)->with('error', 'This swap request has already been sent!');

		// create database entry
		$swap->user_id = Auth::id();
		$swap->hash = $hash;
		$swap->original_shift_id = $org;
		$swap->request_shift_id = $req;
		$swap->save();
		//IMPORTANT: only mail swap requests to possible users, i. e. not to the user who is already subscribed to the original shift
		// user who is on the original shift
		$other_user_org = $shift_org->get_other_user(Auth::id());
		// users who should receive an email
		if ($other_user_org)
			$users = $shift_request->users->filter(function($user) use($other_user_org)
			{
				return $user->id != $other_user_org->id;
			});
		else
			$users = $shift_request->users;

		// mail content
		$subject = 'Swap Request from ' . Auth::user()->get_full_name();
		$msg = "Hello [USER],\r\n\r\n";
		$msg.= Auth::user()->first_name . ' wants to swap shifts. ' . Auth::user()->first_name . ' is assigned to the shift on ' . date("l, jS F Y, \s\\t\a\\r\\t\i\\n\g \a\\t H:i", strtotime($shift_org->start)) . ' and wants to change to your shift on ' . date("l, jS F Y, \s\\t\a\\r\\t\i\\n\g \a\\t H:i", strtotime($shift_request->start)) . ".\r\n";
		$msg.= 'You can view the swap request for the related beamtime in detail here: ' . Request::root() . '/swaps/' . $hash . "\r\n\r\n";
		$msg.= 'A2 Beamtime Scheduler';
		// check if mailing worked
		$success = true;

		// send the mail to every user who should receive it
		$users->each(function($user) use(&$success, $subject, $msg)
		{
			$success &= $user->mail($subject, str_replace(array('[USER]'), array($user->first_name), $msg));
		});

		if ($success)
			return Redirect::to('beamtimes/' . $beamtime_id)->with('success', 'Swap request sent successfully to ' . implode(' and ', $users->lists('first_name')));
		else {
			$swap->delete();  // delete this request in case the mail(s) could not be send
			return Redirect::to('beamtimes/' . $beamtime_id)->with('error', 'Swap request couldn\'t be sent, mailing error...');
		}

/*echo Request::root();
echo '<br />';
echo url();
echo '<br />';
echo asset('/');
echo '<br />';
echo URL::to('/'); //App::make('url')->to('/');
echo '<br />';
echo Request::getHost();
echo '<br />';
echo Request::getHttpHost();
echo '<br />';
echo $_SERVER['HTTP_HOST'];
echo '<br />';
echo $_SERVER['SERVER_NAME'];
echo '<br />';*/

	}


	/**
	 * Display the current swap request with the BeamtimesController
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if (!$swap = $this->swap->whereHash($id)->first())
			return Redirect::to('')->with('warning', 'This swap request is not available. It may have already been performed.');

		$beamtime = Beamtime::find(Shift::find($swap->original_shift_id)->beamtime->id);
		$shifts = $beamtime->shifts;

		return View::make('beamtimes.swap')->with('beamtime', $beamtime)->with('shifts', $shifts)
					->with('swap', $id)->with('org', $swap->original_shift_id)->with('req', $swap->request_shift_id);
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
		if (!$swap = $this->swap->whereHash($id)->first())
			return Redirect::to('')->with('warning', 'This swap request is not available. It may have already been performed.');

		$shift_org = Shift::find($swap->original_shift_id);
		$shift_req = Shift::find($swap->request_shift_id);
		$user_id = $swap->user_id;

		// check if the requesting user is still on the original shift
		if (!$shift_org->users->find($user_id)) {
			// delete the swap request
			$swap->delete();
			return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('warning', 'Swap request deleted, ' . User::find($user_id)->first_name . ' wanted to swap a shift, but unsubscribed from it.');
		}

		// check if the current user is on the requested shift
		if (!$shift_req->users->find(Auth::id())) {
			// delete the swap request
			$swap->delete();
			return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('error', 'Swap request deleted, you unsibscribed from the shift since the swap request has been submitted!');
		}

		// additionally check if the current user is not subscribed to the original shift
		if ($shift_org->users->find(Auth::id())) {
			// delete the swap request
			$swap->delete();
			return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('error', 'Swap request deleted, you cannot swap to a shift where you\'re already assigned to!');
		}

		// if everything is okay, swap the users
		$shift_org->users()->detach($user_id);
		$shift_org->users()->attach(Auth::id());
		$shift_req->users()->detach(Auth::id());
		$shift_req->users()->attach($user_id);

		// delete the swap request
		$swap->delete();

		return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('success', 'Shift workers swapped successfully.');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
