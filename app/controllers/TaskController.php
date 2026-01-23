<?php

namespace Controllers;

use Exception;
use Models\DTO\TaskCompleteRequest;
use Services\TaskService;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(){
        $this->taskService = new TaskService();
    }

    public function getAll() {
        try {
            $this->respond($this->taskService->getAllTasks());
        } catch (Exception $e) {
            $this->respondWithError($e->getMessage(), $e->getCode());
        }
    }

    public function complete(){
        try {
            $request = $this->requestObjectFromPostedJson(TaskCompleteRequest::class);

            if (!isset($request->company_id) || !isset($request->task_id)) {
                $this->respondWithError(400, "Missing required fields");
            }

            $response = $this->taskService->completeTask($request);
            $this->respond($response);
        } catch (Exception $e) {
            $this->respondWithError($e->getMessage(), $e->getCode());
        }
    }
}
