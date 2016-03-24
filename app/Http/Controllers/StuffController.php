<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;


class StuffController extends Controller
{
    // return the stuff associated to this id
    public function getStuff($id) {
    	$content = DB::select("SELECT * FROM Stuff WHERE id = ?",[$id]);

    	if (empty($content)) {
    		return response()->json([ "error"=>"No Stuff correspond to this id in the database"], 404);
    	}

        return response()->json($content[0], 200);    
    }

    public function createStuff(Request $request){
    	$content = DB::insert("INSERT INTO `Stuff`(`name`, `event`, `owner`) VALUES (?, ?, ?)",
    							[$request->input('name'),
    							 $request->input('event'),
    							 $request->input('owner')]);

    	if ($content == 1) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, new stuff couldn't be created"], 400);
        }
    }

    public function deleteStuff(Request $request){

    	// If no id is specified in the Request we throw an error as the delete wouldn't work
    	if (!$request->input('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
    	}

    	$content = DB::delete("DELETE FROM `Stuff` WHERE id = ?",
    							[$request->input('id')]);

    	if ($content == 1) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, stuff couldn't be deleted"], 400);
        }
    }

    // Update a stuff
    // Require to have a id argument in the request
    public function updateStuff(Request $request) {

    	// If no id is specified in the Request we throw an error as the update wouldn't work
    	if (!$request->input('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
    	}

    	// set the first change to an id=id so every request after can start with a coma which facilitate formating
    	$setString = "id=?";
    	$changes = array($request->input('id'));

    	// If the request contain something in the name argument we change the name
    	if ($request->input('name')) {
    		$setString .= ",name = ?";
    		array_push($changes, $request->input('name'));
    	}

    	// Same goes for the owner
    	if ($request->input('owner')) {
    		$setString .= ",owner = ?";
    		array_push($changes, $request->input('owner'));
    	}

    	array_push($changes, $request->input('id'));

    	$result = DB::update("UPDATE `Stuff` SET ".$setString." WHERE id = ?", $changes);

    	if ($result == 1) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, stuff couldn't be updated"], 400);
        }

    }

}
