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
			$shift->users()->attach(Auth::user()->id);
			// shorter
			//Shift::find($shift_id)->users()->attach($user_id);
			$msg = 'Subscribed to shift';
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
