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
	//return View::make('hello');
	return View::make('home');
});

/*Route::get('users', 'UsersController@index');
Route::get('users/{username}','UsersController@show');
Route::get('users/{username}/edit', 'UsersController@edit');
Route::get('users/create', 'UsersController@create');
Route::get('users/{username}/delete', 'UsersController@destroy');*/

Route::resource('users', 'UsersController');

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
//only accessable if logged in
Route::get('admin', function()
{
	return 'Admin Page';
})->before('auth');

// allow only logged in users to use the methods POST, PUT and DELETE of the UsersController
//Route::when('users', 'auth', array('post', 'put', 'delete'));
// without post it's not possible to create accounts
Route::when('users', 'auth', array('put', 'delete'));

// group all routes to controllers etc which should only be accessible when authenticated
// using Route::resource(...)->before('auth'); doesn't work
Route::group(array('before' => 'auth'), function()
{
	Route::resource('beamtimes', 'BeamtimesController');
	Route::patch('shifts/{shifts}', array('as' => 'shifts.update', 'uses' => 'ShiftsController@update'));
	Route::post('shifts/{shifts}/swap', array('as' => 'shifts.swap', 'uses' => 'ShiftsController@swap'));
});
//Route::resource('beamtimes', 'BeamtimesController')->before('auth');

// einfacher: Route::resource('users', 'UsersController');
// nested resources: Route::ressource('users.beamtimes', 'BeamtimesController');
//	--> zwei wildcards --> function show($userId, $beamtimeId)
// php artisan routes (show all routes)
// spezieller: Query string ?...
//	?rpp=5 (5 results per page)
//	?interval=7 (interval in the last 7 days)
//	?cat=green (things with green)

