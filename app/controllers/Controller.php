<?php

namespace Controllers;

class Controller
{
    function respond($data)  : void
    {
        $this->respondWithCode(200, $data);
    }

    function respondWithError($httpCode, $message) : void
    {
        $data = array('errorMessage' => $message);
        $this->respondWithCode($httpCode, $data);
    }

    private function respondWithCode($httpCode, $data) : void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    function requestObjectFromPostedJson($className)
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);

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
