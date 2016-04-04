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
                                AND o.type = 'task'
                                AND o.user = u.id",[$id]);

        $task[0]->owners = $owners;

        return response()->json($task[0], 200);    
    }


    //Create a task
    public function createTask(Request $request){

        if (!$request->json()->get('name') || !$request->json()->get('event')) {
            return response()->json([ "error"=>"Error, new task couldn't be created : you need to specify a name and an event id"],400);
        }

        $id = DB::table('Task')->insertGetId([
                                "name" => $request->json()->get('name'),
                                "event" => $request->json()->get('event'),
                                "completed" => 0]);

    	if ($id > 0) {
            return response()->json(['id' => $id, 'msg' => 'Task successfully created'], 200);

        } else {
            return response()->json([ "error"=>"Error, new task couldn't be created"], 400);
        }
    }

    public function deleteTask($id){
    	// If no id is specified in the Request we throw an error as the delete wouldn't work
    	if (!$id) {
            return response()->json([ "error"=>"Error, no id was specified. You need an id to delete a Task."], 400);
    	}

        $ownerDelete = DB::delete("DELETE FROM `Owner` WHERE item = ? AND type = ?",
                                [$id,
                                 "task"]);

    	$taskDelete = DB::delete("DELETE FROM `Task` WHERE id = ?",
    							[$id]);

    	if ($taskDelete == 1) {
            return response()->json(['msg' => 'Task successfully deleted'], 200);

        } else {
            return response()->json([ "error"=>"Error, task couldn't be deleted"], 400);
        }
    }

    public function addOwner(Request $request){
        if (!$request->json()->get('task') || !$request->json()->get('owner')) {
            return response()->json([ "error"=>"Error, no id was specified for the task or the owner"], 400);
        }

        $exist = DB::table("Owner")->where([
                                        'item' => $request->json()->get('task'),
                                        'user' => $request->json()->get('owner'),
                                        'type' => 'task',
                                        ])->first();

        if (!empty($exist)) {
            return response()->json([ "error"=>"Error, couldn't add an owner to this task. This link is already present in the database."], 400);
        }

        $owner = DB::table("Owner")->insert([
                                        'item' => $request->json()->get('task'),
                                        'user' => $request->json()->get('owner'),
                                        'type' => 'task',
                                        ]);
        

        if ($owner) {
            return response()->json(['msg' => 'Owner successfully added'], 200);

        } else {
            return response()->json([ "error"=>"Error, couldn't add an owner to this task"], 400);
        }
    }

    public function deleteOwner(Request $request){
        if (!$request->json()->get('task') || !$request->json()->get('owner')) {
            return response()->json([ "error"=>"Error, no id was specified for the task or the owner"], 400);
        }

        $owner = DB::table("Owner")->where([
                                        'item' => $request->json()->get('task'),
                                        'user' => $request->json()->get('owner'),
                                        'type' => 'task',
                                        ])
                                    ->delete();

        if ($owner) {
            return response()->json(['msg' => 'Owner successfully deleted'], 200);

        } else {
            return response()->json([ "error"=>"Error, couldn't delete an owner to this task"], 400);
        }

    }


    // Update a stuff
    // Require to have a id argument in the request
    public function updateTask(Request $request) {

    	// If no id is specified in the Request we throw an error as the update wouldn't work
    	if (!$request->json()->get('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
    	}

    	if ($result == 1) {
            return response()->json('Success', 200);

        } else {
            return response()->json([ "error"=>"Error, stuff couldn't be updated"], 400);
        }

    }

}
