<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('(.*)', function () use ($app) {
    return $app->version();
});


// User methods

$app->get('users', 'UserController@getUsers');

$app->get('user/{id}', 'UserController@getUser');

$app->get('user/{id}/events', 'UserController@getEventsOverview');

$app->post('user/new', 'UserController@createUser');

$app->delete('user/delete', 'UserController@deleteUser');

// Event methods

$app->get('event/{eventId}', 'EventController@getEvent');

$app->get('event/link/{eventLink}', 'EventController@getEventByLink');
 
$app->get('event/{eventId}/stuffs', 'EventController@getStuffs');

$app->get('event/{eventId}/attendees', 'EventController@getAttendees');

$app->post('event/new', 'EventController@createEvent');

$app->delete('event/delete', 'EventController@deleteEvent');

$app->put('event/update', 'EventController@updateEvent');

$app->post('event/attendance/new', 'EventController@addAttendee');

$app->put('event/attendance/update', 'EventController@updateAttendee');

// Stuff methods

$app->get('stuff/{id}', 'StuffController@getStuff');

$app->post('stuff/new', 'StuffController@createStuff');

$app->put('stuff/update', 'StuffController@updateStuff');

$app->delete('stuff/delete/{id}', 'StuffController@deleteStuff');

// Task methods

$app->get('task/{id}', 'TaskController@getTask');

$app->post('task/new', 'TaskController@createTask');

$app->delete('task/delete/{id}', 'TaskController@deleteTask');

//$app->put('task/update', 'TaskController@updateTask');


$app->post('task/addOwner', 'TaskController@addOwner');

$app->delete('task/delOwner', 'TaskController@deleteOwner');

// Attendee methods

$app->post('attendee/new', 'AttendeeController@createAttendee');

$app->delete('attendee/delete', 'AttendeeController@deleteAttendee');

$app->put('attendee/update', 'AttendeeController@updateAttendee');

//Auth Method

$app->post('auth/login', 'AuthController@loginMail');


