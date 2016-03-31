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

// group all routes to controllers etc which should only be accessible when authenticated
Route::group(array('before' => 'auth'), function()
{
	Route::get('users/enable', array('as' => 'users.new', 'uses' => 'UsersController@viewNew'));
	Route::patch('users/{users}/enable', array('as' => 'users.enable', 'uses' => 'UsersController@enable'));
	Route::get('users/{users}/shifts', array('as' => 'users.shifts', 'uses' => 'UsersController@shifts'));
	Route::patch('users/{users}/radiation', array('as' => 'users.radiation', 'uses' => 'UsersController@renewRadiationInstruction'));
	Route::get('users/manage', array('as' => 'users.manage', 'uses' => 'UsersController@manageUsers'));
	Route::get('users/admins', array('as' => 'users.admins', 'uses' => 'UsersController@viewAdmins'));
	Route::get('users/radiation_experts', array('as' => 'users.radiation_experts', 'uses' => 'UsersController@viewRadiationExperts'));
	Route::get('users/run_coordinators', array('as' => 'users.run_coordinators', 'uses' => 'UsersController@viewRunCoordinators'));
	Route::get('users/principle_investigators', array('as' => 'users.principle_investigators', 'uses' => 'UsersController@viewPrincipleInvestigators'));
	Route::get('users/radiation', array('as' => 'users.radiation', 'uses' => 'UsersController@viewRadiationInstruction'));
	Route::patch('users/{users}/admin', array('as' => 'users.toggleAdmin', 'uses' => 'UsersController@toggleAdmin'));
	Route::patch('users/{users}/re', array('as' => 'users.toggleRadiationExpert', 'uses' => 'UsersController@toggleRadiationExpert'));
	Route::patch('users/{users}/rc', array('as' => 'users.toggleRunCoordinator', 'uses' => 'UsersController@toggleRunCoordinator'));
	Route::patch('users/{users}/pi', array('as' => 'users.togglePrincipleInvestigators', 'uses' => 'UsersController@togglePrincipleInvestigator'));

	Route::resource('beamtimes', 'BeamtimesController');
	Route::get('statistics', array('as' => 'statistics', 'uses' => 'BeamtimesController@statistics'));
	Route::get('statistics/{year}', array('as' => 'statistics', 'uses' => 'BeamtimesController@statistics'));
	Route::post('statistics/{year}', array('as' => 'statistics', 'uses' => 'BeamtimesController@statistics'));
	Route::get('beamtimes/{id}/rc', array('as' => 'beamtimes.rc_show', 'uses' => 'BeamtimesController@rc_show'));
	Route::patch('beamtimes/{id}/rc', array('as' => 'beamtimes.rc_update', 'uses' => 'BeamtimesController@rc_update'));
	Route::patch('shifts/{shifts}', array('as' => 'shifts.update', 'uses' => 'ShiftsController@update'));
	Route::post('swaps/{shift_org_id}', array('as' => 'swaps.create', 'uses' => 'SwapsController@create'));
	Route::post('swaps/{shift_org_id}/{shift_req_id}', array('as' => 'swaps.store', 'uses' => 'SwapsController@store'));
	Route::get('swaps/{swap}', array('as' => 'swaps.show', 'uses' => 'SwapsController@show'));
	Route::put('swaps/{swap}', array('as' => 'swaps.update', 'uses' => 'SwapsController@update'));
});
// the following routes have to be _after_ all the other users/something routes above, otherwise they will be caught earlier by the show method of the UsersController
Route::resource('users', 'UsersController');

