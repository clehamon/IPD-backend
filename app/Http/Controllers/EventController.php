<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class EventController extends Controller
{
    
	// return all information about an event plus the stuffs and users linked to it from it's ID
    public function getEvent($eventId) {

        $event = DB::select("SELECT * FROM Event WHERE id = ?",[$eventId]);

        if (empty($event)) {
            return response()->json([ "error"=>"Error, no event correspond to this id"],400);
        }

        $event = $event[0];

        return response()->json($this->returnEvent($event));

       
    }

    // return all information about an event plus the stuffs and users linked to it from the linkId
    public function getEventByLink($eventLink) {

        $event = DB::select("SELECT * FROM Event WHERE linkId = ?",[$eventLink]);

        if (empty($event)) {
            return response()->json([ "error"=>"Error, no event correspond to this link"],400);
        }

        $event = $event[0];

        return response()->json($this->returnEvent($event));
    }

    public function returnEvent($event){

        $stuffs = DB::select("SELECT * FROM Stuff WHERE event = ?",[$event->id]);
        
        $attendee = DB::select("SELECT u.id, u.firstName, u.lastName, u.avatar FROM User as u, Attendee as a
                                WHERE a.eventId = ?
                                AND a.userId = u.id",[$event->id]);

        $tasksWithoutOwners = DB::select("  SELECT t.id, t.name, t.completed 
                                            FROM Task as t
                                            WHERE t.event = ?",
                                            [$event->id]);



        //Give a row for each couple task/owner linked to this event
        $tasksWithOwners = DB::select("SELECT t.id, t.name, t.completed, u.firstName, u.lastName, u.avatar, u.id as ownerId FROM Owner as o, Task as t, User as u
                                WHERE t.event = ?
                                AND o.item = t.id
                                AND o.user = u.id
                                AND o.type = ?",[$event->id, "task"]);

        $allTasks = array_merge($tasksWithoutOwners, $tasksWithOwners);
        $tasks = array();

        // Here we group the similar tasks together and put the different owner on an 'owners' array as an attribute of the task
        foreach ($allTasks as $task) {
            if (isset($task->ownerId)) {
                $owner = array('id' => $task->ownerId,
                                'lastName' => $task->lastName,
                                'firstName' => $task->firstName,
                                'avatar' => $task->avatar );

                if (array_key_exists($task->id, $tasks)) {
                    array_push($tasks[$task->id]["owners"], $owner); 
                } else{
                    $currentTask = array('id' => $task->id,
                                        'name' => $task->name,
                                        'completed' => $task->completed,
                                        'owners' => [$owner]
                                        );
                    $tasks[$task->id] = $currentTask;
                }
            } else {
                $currentTask = array('id' => $task->id,
                                    'name' => $task->name,
                                    'completed' => $task->completed,
                                    'owners' => []
                                    );
                $tasks[$task->id] = $currentTask;
            }
        }

        $event->stuffs = $stuffs;
        $event->tasks = array_values($tasks);
        $event->attendee = $attendee;

        return $event;
    }

    public function createEvent(Request $request) {

        if (!$request->json()->get('name')) {
            return response()->json([ "error"=>"Error, new event couldn't be created : you need to specify a name"],400);
        }

        if (!$request->json()->get('admin')) {
            return response()->json([ "error"=>"Error, new event couldn't be created : you need to specify an admin id"],400);
        }
        $linkId = md5($request->json()->get("name").time());
        $id = DB::table('Event')->insertGetId(
                    ["name" => $request->json()->get("name"),
                    "linkId" => $linkId,
                    "date" => $request->json()->get("date"),
                    "time" => $request->json()->get("time"),
                    "duration" => $request->json()->get("duration"),
                    "locationName" => $request->json()->get("locationName"),
                    "locationLat" => $request->json()->get("locationLat"),
                    "locationLong" => $request->json()->get("locationLong"),
                    "description" => $request->json()->get("description"),
                    "coverPicture" => $request->json()->get("coverPicture"),
                    "spotifyPlaylist" => $request->json()->get("spotifyPlaylist")]);

        if ($id !== 0) {

            //TODO Check if the user actually exist in the db

            $admin = DB::table('Attendee')->insertGetId([
                    "userId" => $request->json()->get('admin'),
                    "eventId" => $id,
                    "going" => 1,
                    "isAdmin" => 1,
                    ]);

            $return = array('id' => $id, "linkId" => $linkId, "msg" => "Event created" );

            return response()->json($return,200);

        } else {
            return response()->json([ "error"=>"Error, new event couldn't be created"],400);
        }
    }

    //Delete an event as well as the Attendees, Tasks and Stuffs linked to it
    public function deleteEvent(Request $request){

        // If no id is specified in the Request we throw an error as the delete wouldn't work
        if (!$request->json()->get('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
        }

        $deletedAttendee = DB::delete("DELETE FROM `Attendee` WHERE eventId = ?",
                  [$request->json()->get('id')]);

        $deletedStuff = DB::delete("DELETE FROM `Stuff` WHERE event = ?",
                  [$request->json()->get('id')]);

        $deletedTask = DB::delete("DELETE FROM `Task` WHERE event = ?",
                  [$request->json()->get('id')]);

        $deletedEvent = DB::delete("DELETE FROM `Event` WHERE id = ?",
                  [$request->json()->get('id')]);

        if ($deletedEvent == 1) {
            return response()->json(array("msg" => "Event deleted" ), 200);

        } else {
            return response()->json([ "error"=>"Error, Event couldn't be deleted"], 400);
        }
    }

    public function updateEvent(Request $request){

        $eventColumns = array("name","linkId","date","time","duration","locationName","locationLat","locationLong","description","coverPicture","spotifyPlaylist");

        if (!$request->json()->get('id')) {
            return response()->json([ "error"=>"Error, new event couldn't be updated : you need to specify an id"], 400);
        }

        $updates = array();
        // We filter to put all the input in the request that are column of an event in an array
        //this array will then be used to perform the update
        foreach ($request->json()->all() as $key => $value) {
            if ( in_array($key, $eventColumns) ) {
                $updates[$key] = $value;
            }
        }

        $result = DB::table('Event')
                        ->where('id',$request->json()->get('id'))
                        ->update($updates);

        if ($result !== 0) {
            return response()->json(array("msg" => "Event updated" ), 200);

        } else {
            return response()->json([ "error"=>"Error, Event couldn't be updated"], 400);
        }
    }

    public function getStuffs($eventId) {

    	$stuffs = DB::select("SELECT * FROM Stuff WHERE event = ?",[$eventId]);

        return response()->json($stuffs,200);
    }

    public function getAttendees($eventId) {

		$attendee = DB::select("SELECT u.id, u.firstName, u.lastName, u.avatar FROM User as u, Attendee as a
        						WHERE a.eventId = ?
        						AND a.userId = u.id",[$eventId]);

        return response()->json($attendee,200);
    }

    //Add an attendee 
    public function addAttendee(Request $request){
        
        if (!$request->json()->get('event') || !$request->json()->get('user')) {
            return response()->json([ "error"=>"Error, an event id and user id need to be specified for a user to join an event"], 400);
        }

        if ($request->json()->get('isAdmin')){
            $isAdmin = $request->json()->get('isAdmin');
        } else {
            $isAdmin = 0;
        }

        $id = DB::table("Attendee")->insertGetId([
                                    "userId" => $request->json()->get('user'),
                                    "eventId" => $request->json()->get('event'),
                                    "isAdmin" => $isAdmin,
                                    "going" => 1,
                                    ]);

        if ($id > 0) {
            $return = array("msg" => "User joined the event" );
            return response()->json($return,200);

        } else {
            return response()->json([ "error"=>"User couldn't join the event"],400);
        }

    }

    public function updateAttendee(Request $request){
        $attendeeColumns = array('isAdmin', 'going');

        if (!$request->json()->get('event') || !$request->json()->get('user')) {
            return response()->json([ "error"=>"Error, an event id and user id need to be specified to update the attendance"], 400);
        }

        $updates = array();
        // We filter to put all the input in the request that are column of an event in an array
        //this array will then be used to perform the update
        foreach ($request->json()->all() as $key => $value) {
            if ( in_array($key, $attendeeColumns) ) {
                $updates[$key] = $value;
            }
        }

        $result = DB::table('Attendee')
                        ->where(['eventId' =>$request->json()->get('event'),
                                'userId' => $request->json()->get('user')])
                        ->update($updates);

        if ($result > 0) {
            return response()->json(array("msg" => "Attendance updated" ), 200);

        } else {
            return response()->json([ "error"=>"Error, Attendance couldn't be updated"], 400);
        }


    }

}
