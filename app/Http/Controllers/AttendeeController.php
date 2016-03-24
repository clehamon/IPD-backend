<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
   
    public function createAttendee(Request $request){
    		$admin = 0;
    	if ($request->input('isAdmin') === 'true') {
    		$admin = 1;
    	}

    	$content = DB::insert("INSERT INTO `Attendee`(`userId`, `eventId`, `going`, `isAdmin`) VALUES (?, ?, ?, ?)",
    							[$request->input('userId'),
    							 $request->input('eventId'),
    							 1,
    							 $admin]);

    	if ($content == 1) {
            return response()->json('Success',200);
        } else {
            return response()->json([ "error"=>"Error, new stuff couldn't be created"],400);
        }
    }

    public function deleteAttendee(Request $request) {
    	$result = DB::delete("DELETE FROM `Attendee` WHERE userId = ? AND eventId = ?",
    						[$request->input('userId'),
    						 $request->input('eventId')]);

    	if ($result == 1) {
            return response()->json('Success',200);
        } else {
            return response()->json([ "error"=>"Error, could not delete this Attendee"],400);
        }
    }

    // Update a stuff
    // Require to have a id argument in the request
    public function updateStuff(Request $request) {

    	// If no id is specified in the Request we throw an error as the update wouldn't work
    	if (!$request->input('eventId') || !$request->input('userId')) {
            return response()->json([ "error"=>"Error, no eventId or userId was specified"], 400);
    	}

    	// set the first change to an id=id so every request after can start with a coma which facilitate formating
    	$setString = "eventId=?";
    	$changes = array($request->input('eventId'));



    	//********* FIGURE OUT HOW BOOLEAN ARE TRANSMITTED TO SEE IF WE TREAT STRING OR INT //////////////

    	// If the request contain something in the going argument we change it's value
    	if ($request->input('going')) {
    		$setString .= ",going = ?";
    		array_push($changes, $request->input('going'));
    	}

    	// Same goes for the isAdmin argument
    	if ($request->input('isAdmin')) {
    		$setString .= ",isAdmin = ?";
    		array_push($changes, $request->input('isAdmin'));
    	}

    	array_push($changes, $request->input('eventId'));
    	array_push($changes, $request->input('userId'));

    	$result = DB::update("UPDATE `Attendee` SET ".$setString." WHERE eventId = ? AND userId = ?", $changes);

    	if ($result == 1) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, attendee couldn't be updated"], 400);
        }

    }
}
