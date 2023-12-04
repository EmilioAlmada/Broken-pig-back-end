<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    // 200

    public function paginatedResponse($data, $page, $total_records, $last_page, $per_page){
        $response = array(
            'status' => 'Success',
            "page" => $page,
            "last_page" => $last_page,
            "per_page" => $per_page,
            "total_records" => $total_records,
            "data" => $data
        );

        return response()->json($response,200);
    }

    public function successResponse($message = 'Respuesta exitosa',$data = null, $status = 'Success'){
        $response = array(
            'status' => $status,
            'message' => $message
        );

        if(!is_null($data)){
            $response = $response + array('data' => $data);
        }

        return response()->json($response,200);
    }

    public function successGet($data = null, $status = 'Success'){
        $response = array('status' => $status);

        if(!is_null($data)){
            $response = $response + array('data' => $data);
        }
        return response()->json($response,200);
    }

    public function successCreation($message = null, $data = null, $status = 'Success'){
        $response = array('status' => $status);

        if(!is_null($message)){
            $response = $response + array('message' => $message);
        }

        if(!is_null($data)){
            $response = $response + array('data' => $data);
        }
        return response()->json($response,201);
    }

    public function apiLoginSuccess($user,$token){
        $response = array(
            'data' => $user,
            'token' => $token
        );

        return response()->json($response,200);
    }

    // 200

    // 400

    public function badRequest($message = null, $status = 'bad request'){
        $response = array('status' => $status);

        if(!is_null($message)){
            $response = $response + array('message' => $message);
        }

        return response()->json($response,400);
    }

    public function notFound($message = null, $status = 'not found'){
        $response = array('status' => $status);

        if(!is_null($message)){
            $response = $response + array('message' => $message);
        }

        return response()->json($response,404);
    }

    public function forbiden($message = null, $status = 'forbiden'){
        $response = array('status' => $status);

        if(!is_null($message)){
            $response = $response + array('message' => $message);
        }

        return response()->json($response,403);
    }

    // 400

    // 500

    public function internalError($message = null, $data = null, $status = 'error'){
        $response = array('status' => $status);

        if(!is_null($message)){
            $response = $response + array('message' => $message);
        }

        if(!is_null($data)){
            $response = $response + array('data' => $data);
        }
        return response()->json($response,500);
    }

    // 500
}
