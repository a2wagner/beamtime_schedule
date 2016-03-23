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
		if (Input::get('action') === 'subscribe') {
			if ($shift->users->count() < $shift->n_crew) {
				if ($shift->users->contains($id))
					Redirect::back()->with('error', "You're subscribed to this shift already!");
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
						return Redirect::back()->with('warning', 'You subscribed to another shift within 24 hours!');
				}
			} else {
				return Redirect::back()->with('error', "The shift you wanted to subscribe to is already full!");
			}
		} elseif (Input::get('action') === 'unsubscribe') {
			$shift->users()->detach(Auth::user()->id);
			$msg = 'Unsubscribed from shift';
		}

		return Redirect::back()->with('success', $msg);
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
