<?php

namespace Models\DTO;

use Models\Company;
use Models\Task;

class TaskResponse
{
    public int $company_id;
    public string $company_name;
    public int $task_id;
    public string $task_name;
    public int $reward;
    public string $completed_at;

    public static function CreateFromCompletion(Company $company, Task $task, int $reward): TaskResponse
    {
        $response = new TaskResponse();
        $response->company_id = $company->id;
        $response->company_name = $company->name;
        $response->task_id = $task->id;
        $response->task_name = "$task->name - $task->category";
        $response->reward = $reward;
        $response->completed_at = date('Y-m-d H:i:s');
        return $response;
    }
}
