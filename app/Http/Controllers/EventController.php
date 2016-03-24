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
        
        $stuffs = DB::select("SELECT * FROM Stuff WHERE event = ?",[$eventId]);
        
        $attendee = DB::select("SELECT u.id, u.firstName, u.lastName, u.avatar FROM User as u, Attendee as a
        						WHERE a.eventId = ?
        						AND a.userId = u.id",[$eventId]);

        $event->stuffs = $stuffs;
        $event->attendee = $attendee;



        return response()->json($event,200);
    }

    // return all information about an event plus the stuffs and users linked to it from the linkId
    public function getEventByLink($eventLink) {

        $event = DB::select("SELECT * FROM Event WHERE linkId = ?",[$eventLink]);

        if (empty($event)) {
            return response()->json([ "error"=>"Error, no event correspond to this id"],400);
        }

        $event = $event[0];
        
        $stuffs = DB::select("SELECT * FROM Stuff WHERE event = ?",[$event->id]);
        
        $attendee = DB::select("SELECT u.id, u.firstName, u.lastName, u.avatar FROM User as u, Attendee as a
                                WHERE a.eventId = ?
                                AND a.userId = u.id",[$event->id]);

        $event->stuffs = $stuffs;
        $event->attendee = $attendee;

        return response()->json($event,200);
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

    public function deleteEvent($id){

        // If no id is specified in the Request we throw an error as the delete wouldn't work
        if (!$request->input('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
        }

        $content = DB::delete("DELETE FROM `Event` WHERE id = ?",
                  [$request->input('id')]);

        if ($content == 1) {
            return response()->json(array("msg" => "Event deleted" ), 200);

        } else {
            return response()->json([ "error"=>"Error, Event couldn't be deleted"], 400);
        }
    }

    public function updateEvent(Request $request){

        $eventColumns = array("name","linkId","date","time","duration","locationName","locationLat","locationLong","description","coverPicture","spotifyPlaylist");

        if (!$request->input('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
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
