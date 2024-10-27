<?php

namespace App\Helpers;

/**
 * this function to handel the response of api endpoint
 * this is implemented if it called
 */

class ApiResponse
{
    static function sendResponse($code=200,$msg=null,$data=null)
    {
        $response=[
            'status'    =>$code,
            'msg'       =>$msg,
            'data'      =>$data,
        ];
        return response()->json($response,$code);
    }
}