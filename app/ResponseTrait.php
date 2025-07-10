<?php

namespace App;

trait ResponseTrait
{
    public static function returnError($msgErorr = "", $errorNumber = 400)
    {

        return response()->json([
            "status" => false,
            "message" => $msgErorr,
            "statusNumber" => $errorNumber
        ]);
    }
    public function returnSuccess($message = "", $status = 200)
    {
        return response()->json([
            "status"       => true,
            "message"      => $message,
            "statusNumber" => $status,
        ], $status);
    }
    public function returnData($message = "", $key = "", $value = [], $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'statusNumber' => $status,
            'data' => [
                $key => $value
            ]
        ], $status);
    }
}
