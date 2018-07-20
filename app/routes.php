<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('home');
});

// aliases for session handling
// force login to use https
#Route::group(['before' => 'force_ssl'], function()
#{
#	Route::get('login', 'SessionsController@create');
#	Route::post('login', 'SessionsController@store');
#});
//TODO Comment the following two lines to force login via ssl
Route::get('login', 'SessionsController@create');
Route::post('login', 'SessionsController@store');
Route::get('logout', 'SessionsController@destroy');
//Route::resource('sessions', 'SessionsController');

// allow only logged in users to use the methods POST, PUT and DELETE of the UsersController
//Route::when('users', 'auth', array('post', 'put', 'delete'));
// without post it's not possible to create accounts
Route::when('users', 'auth', array('put', 'delete'));

// iCal link should be accessible without login
Route::get('ical/{hash}', array('as' => 'ical', 'uses' => 'UsersController@ical'));
// group all routes to controllers etc which should only be accessible when authenticated
Route::group(array('before' => 'auth'), function()
{
	Route::patch('ical/{users}', array('as' => 'ical.generate', 'uses' => 'UsersController@generate_ical'));
	Route::get('users/enable', array('as' => 'users.new', 'uses' => 'UsersController@viewNew'));
	Route::patch('users/{users}/enable', array('as' => 'users.enable', 'uses' => 'UsersController@enable'));
	Route::get('users/{users}/shifts', array('as' => 'users.shifts', 'uses' => 'UsersController@shifts'));
	Route::get('users/{users}/ics', array('as' => 'users.ics', 'uses' => 'UsersController@ics'));
	Route::patch('users/{users}/radiation', array('as' => 'users.radiation', 'uses' => 'UsersController@renewRadiationInstruction'));
	Route::patch('users/{users}/retirement', array('as' => 'users.retirement', 'uses' => 'UsersController@setRetirementDate'));
	Route::patch('users/{users}/start_date', array('as' => 'users.start_date', 'uses' => 'UsersController@setStartDate'));
	Route::get('users/manage', array('as' => 'users.manage', 'uses' => 'UsersController@manageUsers'));
	Route::get('users/admins', array('as' => 'users.admins', 'uses' => 'UsersController@viewAdmins'));
	Route::get('users/radiation_experts', array('as' => 'users.radiation_experts', 'uses' => 'UsersController@viewRadiationExperts'));
	Route::get('users/retirement_status', array('as' => 'users.retirement_status', 'uses' => 'UsersController@viewRetirementStatus'));
	Route::get('users/start_date', array('as' => 'users.start_date', 'uses' => 'UsersController@viewStartDate'));
	Route::get('users/run_coordinators', array('as' => 'users.run_coordinators', 'uses' => 'UsersController@viewRunCoordinators'));
	Route::get('users/principle_investigators', array('as' => 'users.principle_investigators', 'uses' => 'UsersController@viewPrincipleInvestigators'));
	Route::get('users/authors', array('as' => 'users.authors', 'uses' => 'UsersController@viewAuthors'));
	Route::get('users/radiation', array('as' => 'users.radiation', 'uses' => 'UsersController@viewRadiationInstruction'));
	Route::get('users/non-kph', array('as' => 'users.nonKPH', 'uses' => 'UsersController@viewNonKPH'));
	Route::get('users/kph', array('as' => 'users.makeKPH', 'uses' => 'UsersController@viewMakeKPH'));
	Route::get('users/kph/{users}', array('as' => 'users.makeKPH', 'uses' => 'UsersController@viewMakeKPH'));
	Route::get('users/settings', array('as' => 'users.settings', 'uses' => 'UsersController@settings'));
	Route::patch('users/settings/{styles}', array('as' => 'users.updateSettings', 'uses' => 'UsersController@updateSettings'));
	Route::patch('users/{users}/admin', array('as' => 'users.toggleAdmin', 'uses' => 'UsersController@toggleAdmin'));
	Route::patch('users/{users}/rs', array('as' => 'users.toggleRetirementStatus', 'uses' => 'UsersController@toggleRetirementStatus'));
	Route::patch('users/{users}/re', array('as' => 'users.toggleRadiationExpert', 'uses' => 'UsersController@toggleRadiationExpert'));
	Route::patch('users/{users}/rc', array('as' => 'users.toggleRunCoordinator', 'uses' => 'UsersController@toggleRunCoordinator'));
	Route::patch('users/{users}/pi', array('as' => 'users.togglePrincipleInvestigators', 'uses' => 'UsersController@togglePrincipleInvestigator'));
	Route::patch('users/{users}/au', array('as' => 'users.toggleAuthors', 'uses' => 'UsersController@toggleAuthor'));
	Route::patch('users/kph', array('as' => 'users.activateKPHaccount', 'uses' => 'UsersController@activateKPHaccount'));
	Route::get('users/merge', array('as' => 'users.merge', 'uses' => 'UsersController@merge'));
	Route::put('users/merge', array('as' => 'users.merge', 'uses' => 'UsersController@mergeAccounts'));
	Route::get('users/password', array('as' => 'users.password', 'uses' => 'UsersController@password'));
	Route::get('users/password/{users}', array('as' => 'users.password', 'uses' => 'UsersController@viewPasswordChange'));
	Route::patch('users/password/{users}', array('as' => 'users.password', 'uses' => 'UsersController@passwordChange'));
	Route::post('users/mail', array('as' => 'users.mail', 'uses' => 'UsersController@mail'));

	Route::get('beamtimes/merge', array('as' => 'beamtimes.merge', 'uses' => 'BeamtimesController@merge'));
	Route::put('beamtimes/merge', array('as' => 'beamtimes.merge', 'uses' => 'BeamtimesController@mergeBeamtimes'));
	Route::resource('beamtimes', 'BeamtimesController');
	Route::get('statistics', array('as' => 'statistics', 'uses' => 'BeamtimesController@statistics'));
	Route::get('statistics/{year}', array('as' => 'statistics', 'uses' => 'BeamtimesController@statistics'));
	Route::post('statistics/{year}', array('as' => 'statistics', 'uses' => 'BeamtimesController@statistics'));
	Route::get('beamtimes/{id}/ics', array('as' => 'beamtimes.ics', 'uses' => 'BeamtimesController@ics'));
	Route::get('beamtimes/{id}/rc', array('as' => 'beamtimes.rc_show', 'uses' => 'BeamtimesController@rc_show'));
	Route::patch('beamtimes/{id}/rc', array('as' => 'beamtimes.rc_update', 'uses' => 'BeamtimesController@rc_update'));
	Route::patch('shifts/{shifts}', array('as' => 'shifts.update', 'uses' => 'ShiftsController@update'));
	Route::post('shifts/{shift}/request', array('as' => 'shifts.request', 'uses' => 'SwapsController@request'));
	Route::patch('swaps/{swap}', array('as' => 'swaps.shift_request', 'uses' => 'SwapsController@store_request'));
	Route::post('swaps/{shift_org_id}', array('as' => 'swaps.create', 'uses' => 'SwapsController@create'));
	Route::post('swaps/{shift_org_id}/{shift_req_id}', array('as' => 'swaps.store', 'uses' => 'SwapsController@store'));
	Route::get('swaps/{swap}', array('as' => 'swaps.show', 'uses' => 'SwapsController@show'));
	Route::put('swaps/{swap}', array('as' => 'swaps.update', 'uses' => 'SwapsController@update'));
});
// the following routes have to be _after_ all the other users/something routes above, otherwise they will be caught earlier by the show method of the UsersController
Route::resource('users', 'UsersController');

