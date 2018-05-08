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
			return Redirect::back()->with('error', 'The shifts don\'t belong to the same beamtime, please use the interface correctly!');
		if (!$shift_org->users->find(Auth::id())->count())
			return Redirect::back()->with('error', 'You\'re not subscribed to the original shift, please use the interface correctly!');

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
		$users = [];
		if (Input::has('user'))
			$users = User::findMany(Input::get('user'));
		elseif ($other_user_org)
			$users = $shift_request->users->filter(function($user) use($other_user_org)
			{
				return $user->id != $other_user_org->id;
			});
		else
			$users = $shift_request->users;

		// mail content
		$subject = 'Swap Request from ' . Auth::user()->get_full_name();
		$msg = "Hello [USER],\r\n\r\n";
		$msg.= Auth::user()->first_name . ' wants to swap shifts. ' . Auth::user()->first_name . ' is assigned to the shift on ' . date("l, jS F Y, \s\\t\a\\r\\t\i\\n\g \a\\t H:i", strtotime($shift_org->start)) . ' and wants to change to a shift slot of your shift on ' . date("l, jS F Y, \s\\t\a\\r\\t\i\\n\g \a\\t H:i", strtotime($shift_request->start)) . ".\r\n";
		$msg.= 'You can view the swap request for the related beamtime in detail here: ' . url() . '/swaps/' . $hash . "\r\n\r\n";
		$msg.= 'A2 Beamtime Scheduler';
		// check if mailing worked
		$success = true;

		// send the mail to every user who should receive it and attach these users to the swap request
		$users->each(function($user) use(&$success, $subject, $msg, $swap)
		{
			$swap->request_users()->attach($user);
			$success &= $user->mail($subject, str_replace(array('[USER]'), array($user->first_name), $msg));
		});

		if ($success)
			return Redirect::to('beamtimes/' . $beamtime_id)->with('success', 'Swap request sent successfully to ' . implode(' and ', $users->lists('first_name')));
		else {
			$swap->delete();  // delete this request in case the mail(s) could not be send
			return Redirect::to('beamtimes/' . $beamtime_id)->with('error', 'Swap request couldn\'t be sent, mailing error...');
		}
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
			// detach the user from the swap request
			$swap->request_users()->detach(Auth::id());
			// delete the swap request if no more users are attached to it
			if (!$swap->request_users()->count()) {
				$swap->delete();
				// inform the original user about the deletion
				$subject = 'Update on Swap Request ' . $id;
				$msg = 'Hello ' . User::find($user_id)->first_name . ",\r\n\r\n";
				$msg.= Auth::user()->first_name . " is no longer subscribed to the shift you requested for swapping. The request was deleted automatically.\r\n";
				$msg.= 'A2 Beamtime Scheduler';
				User::find($user_id)->mail($subject, $msg);

				return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('error', 'Swap request deleted, you unsubscribed from the shift since the swap request has been submitted!');
			}
			return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('error', 'You unsubscribed from the shift since the swap request has been submitted!');
		}
		// additionally check if the current user is not subscribed to the original shift
		if ($shift_org->users->find(Auth::id())) {
			// detach the user from the swap request
			$swap->request_users()->detach(Auth::id());
			// delete the swap request if no more users are attached to it
			if (!$swap->request_users()->count()) {
				$swap->delete();
				// inform the original user about the deletion
				$subject = 'Update on Swap Request ' . $id;
				$msg = 'Hello ' . User::find($user_id)->first_name . ",\r\n\r\n";
				$msg.= Auth::user()->first_name . " is now subscribed to the same shift you wanted to swap from. The request was deleted automatically.\r\n";
				$msg.= 'A2 Beamtime Scheduler';
				User::find($user_id)->mail($subject, $msg);

				return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('error', 'Swap request deleted, you cannot swap to a shift where you\'re already assigned to!');
			}
			return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('error', 'You cannot swap to a shift where you\'re already assigned to!');
		}

		if (Input::get('action') === 'decline') {  // detach user from the swap request, inform requesting user
			$swap->request_users()->detach(Auth::id());
			// delete the swap request if no more users are attached to it
			if (!$swap->request_users()->count()) {
				$swap->delete();
				// inform the original user about the deletion
				$subject = 'Update on Swap Request ' . $id;
				$msg = 'Hello ' . User::find($user_id)->first_name . ",\r\n\r\n";
				$msg.= Auth::user()->first_name . " declined your swap request. Since he was the last attached user to this request, it was deleted automatically.\r\n";
				$msg.= 'A2 Beamtime Scheduler';
				User::find($user_id)->mail($subject, $msg);

				return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('success', 'You have been detached from the swap request successfully, request deleted.');
			}
			// inform the original user about the update
			$subject = 'Update on Swap Request ' . $id;
			$msg = 'Hello ' . User::find($user_id)->first_name . ",\r\n\r\n";
			$msg.= Auth::user()->first_name . " declined your swap request. There is still a user attached to it, so this request is pending.\r\n";
			$msg.= 'You can view the swap request for the related beamtime in detail here: ' . url() . '/swaps/' . $id . "\r\n\r\n";
			$msg.= 'A2 Beamtime Scheduler';
			User::find($user_id)->mail($subject, $msg);

			return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('success', 'You have been detached from the swap request successfully.');
		} else {  // perform swap request
			// if everything is okay, swap the users
			$shift_org->users()->detach($user_id);
			$shift_org->users()->attach(Auth::id());
			$shift_req->users()->detach(Auth::id());
			$shift_req->users()->attach($user_id);

			// delete the swap request
			$swap->delete();

			$extend_notice = "";
			// check if the requested shift is a one person shift and extend it if needed due to not being experienced enough
			$experienced = User::find($user_id)->experienced($shift_req);
			if ($shift_req->users()->count() === 1 && $shift_req->n_crew === 1 && !$experienced) {
				$shift_req->n_crew = 2;
				$shift_req->save();
				$extend_notice = " The shift has been modified for two persons due to your low shift experience.";
			}

			// inform the original user about the update
			$subject = 'Update on Swap Request ' . $id;
			$msg = 'Hello ' . User::find($user_id)->first_name . ",\r\n\r\n";
			$msg.= Auth::user()->first_name . " accepted your swap request. The swap has been performed successfully." . $extend_notice . "\r\n\r\n";
			$msg.= 'A2 Beamtime Scheduler';
			User::find($user_id)->mail($subject, $msg);

			$ret_note = '';
			// check if the original shift is a one person shift and extend it if needed due to not being experienced enough
			$experienced = Auth::user()->experienced($shift_org);
			if ($shift_org->users()->count() === 1 && $shift_org->n_crew === 1 && !$experienced) {
				$shift_org->n_crew = 2;
				$shift_org->save();
				$ret_note = ' Shift modified for two persons due to low shift experience';
			}

			return Redirect::to('beamtimes/' . $shift_org->beamtime->id)->with('success', 'Shift workers swapped successfully.' . $ret_note);
		}
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


	/**
	 * Request a shift slot from other users which have subscribed to a shift.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function request($id)
	{
		$shift = Shift::find($id);
		$beamtime_id = $shift->beamtime->id;
		if (!empty($shift->users->find(Auth::id())))
			return Redirect::back()->with('error', 'You\'re subscribed to the requested shift already, please use the interface correctly!');

		// the query seems reasonable, create an entry in the database for this request and send emails to the users of this shift
		$swap = new Swap();
		$hash = $swap->create_hash(Auth::id(), 0, $id);

		// check if this request is already in the database
		if (Swap::whereHash($hash)->count())
			return Redirect::to('beamtimes/' . $beamtime_id)->with('error', 'This shift request has been sent already!');

		// create database entry
		$swap->user_id = Auth::id();
		$swap->hash = $hash;
		$swap->original_shift_id = $id;
		$swap->request_shift_id = $id;
		$swap->save();

		// mail content
		$subject = 'Shift Request from ' . Auth::user()->get_full_name();
		$msg = "Hello [USER],\r\n\r\n";
		$msg.= Auth::user()->first_name . ' wants to take a shift slot of the shift on ' . date("l, jS F Y, \s\\t\a\\r\\t\i\\n\g \a\\t H:i", strtotime($shift->start)) . " you're subscribed to.\r\n";
		$msg.= 'You can view the shift request for the related beamtime in detail here: ' . url() . '/swaps/' . $hash . "\r\n\r\n";
		$msg.= 'A2 Beamtime Scheduler';
		// check if mailing worked
		$success = true;

		// get the users who should receive the request
		$users = [];
		if (Input::has('user'))
			$users = User::findMany(Input::get('user'));
		else
			$users = $shift->users;

		// send the mail to every user who should receive it and attach these users to the swap request
		$users->each(function($user) use(&$success, $subject, $msg, $swap)
		{
			$swap->request_users()->attach($user);
			$success &= $user->mail($subject, str_replace(array('[USER]'), array($user->first_name), $msg));
		});

		if ($success)
			return Redirect::back()->with('success', 'Shift request sent successfully to ' . implode(' and ', $users->lists('first_name')));
		else {
			$swap->delete();  // delete this request in case the mail(s) could not be sent
			return Redirect::back()->with('error', 'Shift request couldn\'t be sent, mailing error...');
		}
	}


	/**
	 * Perform the shift request.
	 *
	 * @param string $hash
	 * @return Response
	 */
	public function store_request($hash)
	{
		if (!$swap = $this->swap->whereHash($hash)->first())
			return Redirect::to('')->with('warning', 'This shift request is not available. It may have already been performed.');

		if (!$swap->is_request())
			return Redirect::to('')->with('error', 'This request is not a shift request. Something might have gone wrong.');

		$shift = Shift::find($swap->request_shift_id);
		$user = User::find($swap->user_id);

		// check if the current user is on the requested shift
		if (!$shift->users->find(Auth::id())) {
			// detach the user from the swap request
			$swap->request_users()->detach(Auth::id());
			// delete the swap request if no more users are attached to it
			if (!$swap->request_users()->count()) {
				$swap->delete();
				// inform the original user about the deletion
				$subject = 'Update on Shift Request ' . $hash;
				$msg = 'Hello ' . $user->first_name . ",\r\n\r\n";
				$msg.= Auth::user()->first_name . " is no longer subscribed to the shift you requested to take. The request was deleted automatically.\r\n";
				$msg.= 'A2 Beamtime Scheduler';
				$user->mail($subject, $msg);

				return Redirect::to('beamtimes/' . $shift->beamtime->id)->with('error', 'Shift request deleted, you unsubscribed from the shift since the shift request has been submitted!');
			}
			return Redirect::to('beamtimes/' . $shift->beamtime->id)->with('error', 'You unsubscribed from the shift since the shift request has been submitted!');
		}

		if (Input::get('action') === 'decline') {  // detach user from the swap request, inform requesting user
			$swap->request_users()->detach(Auth::id());
			// delete the swap request if no more users are attached to it
			if (!$swap->request_users()->count()) {
				$swap->delete();
				// inform the original user about the deletion
				$subject = 'Update on Shift Request ' . $hash;
				$msg = 'Hello ' . $user->first_name . ",\r\n\r\n";
				$msg.= Auth::user()->first_name . " declined your shift request. Since he was the last attached user to this request, it was deleted automatically.\r\n";
				$msg.= 'A2 Beamtime Scheduler';
				$user->mail($subject, $msg);

				return Redirect::to('beamtimes/' . $shift->beamtime->id)->with('success', 'You have been detached from the shift request successfully, request deleted.');
			}
			// inform the original user about the update
			$subject = 'Update on Shift Request ' . $hash;
			$msg = 'Hello ' . $user->first_name . ",\r\n\r\n";
			$msg.= Auth::user()->first_name . " declined your shift request. There is still a user attached to it, so this request is pending.\r\n";
			$msg.= 'You can view the shift request for the related beamtime in detail here: ' . url() . '/swaps/' . $hash . "\r\n\r\n";
			$msg.= 'A2 Beamtime Scheduler';
			$user->mail($subject, $msg);

			return Redirect::to('beamtimes/' . $shift->beamtime->id)->with('success', 'You have been detached from the shift request successfully.');
		} else {  // perform swap request
			// if everything is okay, change the users
			$shift->users()->detach(Auth::id());
			$shift->users()->attach($user->id);

			// delete the swap request
			$swap->delete();

			$extend_notice = "";
			// check if the requested shift is a one person shift and extend it if needed due to not being experienced enough
			$experienced = $user->experienced($shift);
			if ($shift->users()->count() === 1 && $shift->n_crew === 1 && !$experienced) {
				$shift->n_crew = 2;
				$shift->save();
				$extend_notice = " The shift has been modified for two persons due to your low shift experience.";
			}

			// inform the requesting user about the update
			$subject = 'Update on Shift Request ' . $hash;
			$msg = 'Hello ' . $user->first_name . ",\r\n\r\n";
			$msg.= Auth::user()->first_name . ' accepted your shift request. You have been successfully subscribed to the shift on ' . date("l, jS F Y, \s\\t\a\\r\\t\i\\n\g \a\\t H:i", strtotime($shift->start)) . "." . $extend_notice . "\r\n\r\n";
			$msg.= 'A2 Beamtime Scheduler';
			$user->mail($subject, $msg);

			return Redirect::to('beamtimes/' . $shift->beamtime->id)->with('success', 'Shift workers exchanged successfully.');
		}
	}


}
