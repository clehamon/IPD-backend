<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
   
    public function createAttendee(Request $request){
    		$admin = 0;
    	if ($request->json()->get('isAdmin') === 'true') {
    		$admin = 1;
    	}

    	$content = DB::insert("INSERT INTO `Attendee`(`userId`, `eventId`, `going`, `isAdmin`) VALUES (?, ?, ?, ?)",
    							[$request->json()->get('userId'),
    							 $request->json()->get('eventId'),
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
    						[$request->json()->get('userId'),
    						 $request->json()->get('eventId')]);

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
    	if (!$request->json()->get('eventId') || !$request->json()->get('userId')) {
            return response()->json([ "error"=>"Error, no eventId or userId was specified"], 400);
    	}

    	// set the first change to an id=id so every request after can start with a coma which facilitate formating
    	$setString = "eventId=?";
    	$changes = array($request->json()->get('eventId'));



    	//********* FIGURE OUT HOW BOOLEAN ARE TRANSMITTED TO SEE IF WE TREAT STRING OR INT //////////////

    	// If the request contain something in the going argument we change it's value
    	if ($request->json()->get('going')) {
    		$setString .= ",going = ?";
    		array_push($changes, $request->json()->get('going'));
    	}

    	// Same goes for the isAdmin argument
    	if ($request->json()->get('isAdmin')) {
    		$setString .= ",isAdmin = ?";
    		array_push($changes, $request->json()->get('isAdmin'));
    	}

    	array_push($changes, $request->json()->get('eventId'));
    	array_push($changes, $request->json()->get('userId'));

    	$result = DB::update("UPDATE `Attendee` SET ".$setString." WHERE eventId = ? AND userId = ?", $changes);

    	if ($result == 1) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, attendee couldn't be updated"], 400);
        }

    }
}
