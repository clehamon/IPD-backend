<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;


class TaskController extends Controller
{
    // return the stuff associated to this id
    public function getTask($id) {
    	$task = DB::select("SELECT * FROM Task WHERE id = ?",[$id]);

    	if (empty($task)) {
    		return response()->json([ "error"=>"No Task correspond to this id"], 404);
    	}


        $owners = DB::select("SELECT u.id, u.firstName, u.lastName, u.avatar FROM User as u, Owner as o
                                WHERE o.item = ?
                                AND o.type = 'stuff'
                                AND o.user = u.id",[$id]);

        $task[0]->owners = $owners;

        return response()->json($task[0], 200);    
    }


    //Create a task
    public function createTask(Request $request){

        if (!$request->input('name') || !$request->input('event')) {
            return response()->json([ "error"=>"Error, new task couldn't be created : you need to specify a name and an event id"],400);
        }

        $id = DB::table('Task')->insertGetId([
                                "name" => $request->input('name'),
                                "event" => $request->input('event'),
                                "completed" => 0]);

    	if ($id > 0) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, new stuff couldn't be created"], 400);
        }
    }

    public function deleteTask(Request $request){

    	// If no id is specified in the Request we throw an error as the delete wouldn't work
    	if (!$request->input('id')) {
            return response()->json([ "error"=>"Error, no id was specified. You need an id to delete a Task."], 400);
    	}

        $ownerDelete = DB::delete("DELETE FROM `Owner` WHERE item = ? AND type = ?",
                                [$request->input('id'),
                                 "task"]);

    	$taskDelete = DB::delete("DELETE FROM `Task` WHERE id = ?",
    							[$request->input('id')]);

    	if ($taskDelete == 1) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, task couldn't be deleted"], 400);
        }
    }

    // Update a stuff
    // Require to have a id argument in the request
    public function updateTask(Request $request) {

    	// If no id is specified in the Request we throw an error as the update wouldn't work
    	if (!$request->input('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
    	}

    	if ($result == 1) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, stuff couldn't be updated"], 400);
        }

    }

}
