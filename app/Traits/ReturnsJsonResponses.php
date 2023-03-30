<?php

namespace App\Traits;

use Illuminate\Support\MessageBag;

trait ReturnsJsonResponses
{
    public function success_response($data = [], $message = "Successful", $http_status = 200, $headers = [])
    {
        $status = true;
        $success_data = compact('status', 'message', 'data');
        return response()->json($success_data, $http_status, $headers);
    }

    public function error_response($error = "An error occurred", $message = "Failed", $http_status = 500, $headers = [])
    {
        $status = false;
        $error_data = compact('status', 'message', 'error');
        return response()->json($error_data, $http_status, $headers);
    }

    public function exception_response(\Exception $exception, string $message = "An error occurred")
    {
        $error_data = [
            "status" => false,
            "message" => $message,
            "errors" => [$exception->getMessage()]
        ];
        return response()->json($error_data, 500);
    }

    public function validation_error_response(MessageBag $messageBag, $http_status = 422, array $headers = [])
    {
        $data = [
            'status' => false,
            'message' => "Validation failed",
            'errors' => $messageBag->all(),
        ];
        return response()->json($data, $http_status, $headers);
    }

    protected function withArray(array $array, int $http_status = 200, array $headers = [], $options = 0)
    {
        return response()->json($array, $http_status, $headers, $options);
    }
}
