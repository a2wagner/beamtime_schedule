<?php

class ShiftsController extends \BaseController {

	protected $shift;

	public function __construct(Shift $shift)
	{
		$this->shift = $shift;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return Redirect::action('BeamtimesController@index');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
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
		$msg = '';
		$shift = Shift::find($id);
		if (Input::get('event') === 'subscribe') {
			if ($shift->users->count() < $shift->n_crew) {
				if ($shift->users->contains(Auth::user()->id))
					return ['error', "You're subscribed to this shift already!"];
				$shift->users()->attach(Auth::user()->id);
				// shorter
				//Shift::find($shift_id)->users()->attach($user_id);
				$msg = 'Subscribed to shift';
				// check if the user subscribed to another shift within 24 hours
				$start = new DateTime($shift->start);
				$end = $shift->end();
				foreach (Auth::user()->shifts as $s) {
					$diffStart = abs($start->getTimestamp() - $s->end()->getTimestamp())/3600;
					$diffEnd = abs($end->getTimestamp() - strtotime($s->start))/3600;
					if (($diffStart < $diffEnd && $diffStart < 24-$shift->duration) || ($diffEnd < $diffStart && $diffEnd < 24-$shift->duration))
						return ['warning', 'You subscribed to another shift within 24 hours!'];
				}
			} else {
				return ['error', "The shift you wanted to subscribe to is already full!"];
			}
		} elseif (Input::get('event') === 'unsubscribe') {
			$shift->users()->detach(Auth::user()->id);
			$msg = 'Unsubscribed from shift';
			// check if the shift will start soon
			$start = new DateTime($shift->start);
			$diff = $start->getTimeStamp() - time();
			$diff /= 86400;
			// if the shift will start in less than 7 days, send an email to the run coordinators of the corresponding day
			if ($diff < 7) {
				$date = strtok($shift->start, ' ');
				$rc = RCShift::where('start', 'LIKE', $date.'%')->get()->user->unique();
				$beamtime = $shift->beamtime;
				// mail content
				$subject = 'Someone unsubscribed from a shift on ' . $date;
				$msg = "Hello [USER],\r\n\r\n";
				$msg.= Auth::user()->get_full_name() . ' unsubscribed from the shift on '. date("l, jS F Y, \s\\t\a\\r\\t\i\\n\g \a\\t H:i", strtotime($shift->start)) . '. This is an automatic notification to inform you of the now free shift starting in ' . round($diff, 1) . " days.\r\n\r\n";
				$msg.= 'You can use the following link to view the corresponding beamtime \'' . $beamtime->name . '\': ' . url() . '/beamtimes/' . $beamtime->id . "\r\n\r\n";
				$msg.= "A2 Beamtime Scheduler";
				$success = true;
				// send the mail to the run coordinators
				$rc->each(function($user) use(&$success, $subject, $msg)
				{
					$success &= $user->mail($subject, str_replace(array('[USER]'), array($user->first_name), $msg));
				});
				return ['warning', 'You unsubscribed from a shift which will start in less than a week!'];
			}
		}

		return ['success', $msg];
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
