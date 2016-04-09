<?php

namespace App\Http\Controllers;

use DB;
use Crypt;
use Socialite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Encryption\DecryptException;


class AuthController extends Controller {

    public function loginMail(Request $request){


        if (!$request->json()->get('email') || !$request->json()->get('password')) {
            return response()->json([ "error"=>"Error, you need to specify an email and password to login"], 400);
        }

        $user = DB::table('User')->select('id','firstName','lastName','email','avatar')
                                ->where('email', $request->json()->get('email'))
                                ->first();

        if (!$user) {
            return response()->json([ "error"=>"No user correspond to this email"], 404);
        }

        // try {
        //     $decrypted = Crypt::decrypt($user->password);
        //     var_dump($decrypted);
            
        // } catch (Exception $e) {
        //     var_dump($e->getMessage());
        // }

        // if ( $request->json()->get('password') != Crypt::decrypt($user->password)) {
        //     return response()->json([ "error"=>"Wrong password"], 400);
        // }

        return response()->json($user,200);
    }
}
