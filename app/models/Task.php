<?php

namespace Models;

class Task
{
    public int $id;
    public string $category;
    public string $name;
    public int $reward_p1;
    public int $reward_p2;
    public int $reward_p3;
    public int $reward_p4;
    public int $reward_p5;
    public int $penalty;

    // Structure: [['company_id' => 1, 'company_name' => 'Haviken', 'completed_at' => '...']]
    public array $finished_by = [];
    public array $failed_by = [];

    public static function fromModel(Task $task): self{
        $response = new self();
        $response->id = $task->id;
        $response->category = $task->category;
        $response->name = $task->name;
        $response->reward_p1 = $task->reward_p1;
        $response->reward_p2 = $task->reward_p2;
        $response->reward_p3 = $task->reward_p3;
        $response->reward_p4 = $task->reward_p4;
        $response->reward_p5 = $task->reward_p5;
        $response->finished_by = $task->finished_by;
        $response->failed_by = $task->failed_by;
        return $response;
    }
}
