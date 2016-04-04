<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{

    // return all Users in the database
    public function getUsers() {

        $content = DB::select("SELECT firstName, lastName, email, avatar, source FROM User");

        return response()->json($content,200);
    }

    // Return one user specified by it's id
    public function getUser($id) {

        $user = DB::select("SELECT firstName, lastName, email, avatar, source FROM User WHERE id = ?",[$id]);

        return response()->json($user[0],200);
    }

    // Create a new user
    public function createUser(Request $request){

        // If some required arguments are missing we throw an error
        if (!$request->json()->get('email') || !$request->json()->get('firstName') || !$request->json()->get('lastName')) {
            return response()->json([ "error"=>"Error, new user couldn't be created : you need to specify a last name, first name and email"],400);
        }

        if ($request->json()->get('avatar') == NULL) {
          $avatar = "http://clementhamon.com/host/avatar-square.jpg";
        } else {
          $avatar = $request->json()->get('avatar');
        }

        $id = DB::table('User')->insertGetId([
                                    "firstName" => $request->json()->get('firstName'),
                                    "lastName" => $request->json()->get('lastName'),
                                    "email" => $request->json()->get('email'),
                                    "password" => md5($request->json()->get('password')),
                                    "avatar" => $avatar,
                                    "source" =>"origin",
                                ]);

        if ($id > 0) {
            return response()->json([ "id" => $id, "msg"=>"User successfully created"],200);

        } else {
            return response()->json([ "error"=>"Error, new user couldn't be created"],409);
        }

        
    }

    // Delete a user from the database based on it's id
    public function deleteUser(Request $request){
      
      // If no id is specified in the Request we throw an error as the delete wouldn't work
        if (!$request->json()->get('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
        }

        $updateStuff = DB::table("Stuff")
                        ->where('owner', $request->json()->get('id'))
                        ->update(["owner" => NULL]);

        $removeAttendee = DB::table('Attendee')
                        ->where('userId', $request->json()->get('id'))
                        ->delete();

        $deletedUser = DB::delete("DELETE FROM `User` WHERE id = ?",
                  [$request->json()->get('id')]);

        if ($deletedUser > 0) {
            return response()->json([ "msg"=>"User successfully deleted"], 200);

        } else {
            return response()->json([ "error"=>"Error, User couldn't be deleted"], 400);
        }
    }

    // Return an overview of the event linked to the user passed in arguement
    // Array of event, each supplied with the list of stuffs linked to the users and the attendee going to the event
    public function getEventsOverview($id){

        $overview = [];

        $events = DB::select(" SELECT Event.id, Event.linkId, Event.name, Event.date, Event.time, Event.locationName as location, Event.coverPicture, 
                                      Attendee.isAdmin, Attendee.going
                                FROM (Event,Attendee)
                                WHERE Attendee.userId = ?
                                AND Attendee.eventId = Event.id",
                                [$id]);

        foreach ($events as $event) {
            $stuffs = DB::select("SELECT s.name, s.owner
                                 FROM Stuff AS s
                                 WHERE s.event = ?
                                 AND s.owner = ?",
                                 [$event->id, $id]);

           $event->stuffs = $stuffs;

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

            $event->tasks = $tasks;

            $attendee = DB::select(" SELECT User.firstName, User.lastName, User.avatar
                                    FROM User, Attendee 
                                    WHERE Attendee.eventId = ?
                                    AND Attendee.userId = User.id
                                    AND Attendee.going = 1",
                                    [$event->id]);


            $event->attendee = $attendee;

            array_push($overview, $event);
        }

        return response()->json($overview,200);
    }

    //
}
