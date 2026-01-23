<?php

namespace Models\DTO;

class TaskResponse
{
    public int $company_id;
    public string $company_name;
    public int $task_id;
    public string $task_name;
    public int $reward;
    public string $completed_at;

    public static function CreateFromCompletion($company, $task, $reward): TaskResponse
    {
        $response = new TaskResponse();
        $response->company_id = $company->company_id;
        $response->company_name = $company->company_name;
        $response->task_id = $task->id;
        $response->task_name = "$task->name - $task->category";
        $response->reward = $reward;
        $response->completed_at = date('Y-m-d H:i:s');
        return $response;
    }
}
