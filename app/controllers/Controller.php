<?php

namespace Controllers;

class Controller
{
    public function ping()
    {
        $this->respond("");
    }

    public function diagnostics(){
        $data = [
            'status' => 'OK',
            'timestamp' => date("Y-m-d H:i:s"),
            'server' => $_ENV['JWT_ISSUER']
        ];
        $this->respond($data);
    }

    protected function respond($data)  : void
    {
        $this->respondWithCode(200, $data);
    }

    protected function respondWithError($httpCode, $message) : void
    {
        $data = array('errorMessage' => $message);
        $this->respondWithCode($httpCode, $data);
    }

    private function respondWithCode($httpCode, $data) : void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit;
    }

    function requestObjectFromPostedJson($className)
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->respondWithError(400, "Invalid JSON provided");
        }

        $object = new $className();
        foreach ($data as $key => $value) {
            if(is_object($value)) {
                continue;
            }
            $object->{$key} = $value;
        }
        return $object;
    }
}
