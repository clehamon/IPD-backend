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

        if (!$request->json()->get('name') || !$request->json()->get('event')) {
            return response()->json([ "error"=>"Error, new stuff couldn't be created : you need to specify a name and an event id"],400);
        }

        $id = DB::table("Stuff")->insertGetId([
                                "name" => $request->json()->get('name'),
                                "event" => $request->json()->get('event'),
                                "owner" => $request->json()->get('owner')]);


    	if ($id > 1) {
            $return = array('id' => $id, "msg" => "Stuff created" );

            return response()->json($return,200);


        } else {
            var_dump($id);
            return response()->json([ "error"=>"Error, new stuff couldn't be created"], 400);
        }
    }

    public function deleteStuff($id){

    	// If no id is specified in the Request we throw an error as the delete wouldn't work
    	if (!$id) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
    	}

    	$content = DB::delete(" DELETE FROM `Stuff` WHERE id = ?",
    							[$id]);

    	if ($content == 1) {
            return response()->json(array('msg' => "Stuff successfully deleted"), 200);

        } else {
            return response()->json([ "error"=>"Error, stuff couldn't be deleted"], 400);
        }
    }

    // Update a stuff
    // Require to have a id argument in the request
    public function updateStuff(Request $request) {

        $columnUpdatable = array('name',"owner");

    	// If no id is specified in the Request we throw an error as the update wouldn't work
    	if (!$request->json()->get('id')) {
            return response()->json([ "error"=>"Error, no id was specified"], 400);
    	}

        $updates = array();
        // We filter to put all the input in the request that are updatable column in an array
        //this array will then be used to perform the update
        foreach ($request->json()->all() as $key => $value) {
            if ( in_array($key, $columnUpdatable) ) {
                $updates[$key] = $value;
            }
        }


    	$result = DB::table("Stuff")
                        ->where('id',$request->json()->get('id'))
                        ->update($updates);

    	if ($result > 0) {
            return response()->json([ "msg"=>"Stuff successfully updated"], 200);

        } else {
            return response()->json([ "error"=>"Error, stuff couldn't be updated"], 400);
        }

    }

}
