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

        //Give a row for each couple task/owner linked to this event
        $tasksResult = DB::select("SELECT t.id, t.name, t.completed, u.firstName, u.lastName, u.avatar, u.id as ownerId FROM Owner as o, Task as t, User as u
                                WHERE t.event = ?
                                AND o.item = t.id
                                AND o.user = u.id
                                AND o.type = ?",[$event->id, "task"]);

        // Here we group the similar tasks together and put the different owner on an 'owners' array as an attribute of the task
        $tasks = array();
        foreach ($tasksResult as $task) {
            $owner = array('id' => $task->ownerId,'lastName' => $task->lastName,'firstName' => $task->firstName,'avatar' => $task->avatar );
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
        }

        $event->stuffs = $stuffs;
        $event->tasks = $tasks;
        $event->attendee = $attendee;

        return $event;
    }

    public function createEvent(Request $request) {

        if (!$request->input('name')) {
            return response()->json([ "error"=>"Error, new event couldn't be created : you need to specify a name"],400);
        }

        $id = DB::table('Event')->insertGetId(
                    ["name" => $request->input("name"),
                    "linkId" => md5($request->input("name").time()),
                    "date" => $request->input("date"),
                    "time" => $request->input("time"),
                    "duration" => $request->input("duration"),
                    "locationName" => $request->input("locationName"),
                    "locationLat" => $request->input("locationLat"),
                    "locationLong" => $request->input("locationLong"),
                    "description" => $request->input("description"),
                    "coverPicture" => $request->input("coverPicture"),
                    "spotifyPlaylist" => $request->input("spotifyPlaylist")]);

        if ($id !== 0) {
            $return = array('id' => $id, "msg" => "Event created" );
            return response()->json($return,200);

        } else {
            return response()->json([ "error"=>"Error, new event couldn't be created"],400);
        }
    }

    //Delete an event as well as the Attendees, Tasks and Stuffs linked to it
    public function deleteEvent(Request $request){

        // If no id is specified in the Request we throw an error as the delete wouldn't work
        if (!$request->input('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
        }

        $deletedAttendee = DB::delete("DELETE FROM `Attendee` WHERE eventId = ?",
                  [$request->input('id')]);

        $deletedStuff = DB::delete("DELETE FROM `Stuff` WHERE event = ?",
                  [$request->input('id')]);

        $deletedTask = DB::delete("DELETE FROM `Task` WHERE event = ?",
                  [$request->input('id')]);

        $deletedEvent = DB::delete("DELETE FROM `Event` WHERE id = ?",
                  [$request->input('id')]);

        if ($deletedEvent == 1) {
            return response()->json(array("msg" => "Event deleted" ), 200);

        } else {
            return response()->json([ "error"=>"Error, Event couldn't be deleted"], 400);
        }
    }

    public function updateEvent(Request $request){

        $eventColumns = array("name","linkId","date","time","duration","locationName","locationLat","locationLong","description","coverPicture","spotifyPlaylist");

        if (!$request->input('id')) {
            return response()->json([ "error"=>"Error, new event couldn't be updated : you need to specify an id"], 400);
        }

        $updates = array();
        // We filter to put all the input in the request that are column of an event in an array
        //this array will then be used to perform the update
        foreach ($request->all() as $key => $value) {
            if ( in_array($key, $eventColumns) ) {
                $updates[$key] = $value;
            }
        }

        $result = DB::table('Event')
                        ->where('id',$request->input('id'))
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

}
