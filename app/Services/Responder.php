<?php


namespace App\Services;


class Responder
{
    static function success(string $message){
        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => $message
        ]);
    }

    static function result(array $items){
        $result = [
            'success' => true,
            'code' => 200,
        ];

        $result = array_merge($result,$items);
        return response()->json($result);
    }

    static function error(string $message){
        return response()->json([
            'success' => false,
            'code' => 400,
            'message' => $message
        ]);
    }

    static function validationError($errors){
        return response()->json([
            'success' => false,
            'code' => -1,
            'message' => 'در اعتبارسنجی اطلاعات مشکلی وجود دارد',
            'errors'  => $errors
        ]);
    }
}
