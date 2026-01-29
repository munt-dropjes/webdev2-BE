<?php

namespace Services;

use Exception;
use Models\DTO\TaskCompleteRequest;
use Models\DTO\TaskResponse;
use Models\Task;
use Repositories\CompanyRepository;
use Repositories\TaskRepository;

class TaskService
{
    private TaskRepository $taskRepo;
    private CompanyRepository $companyRepo;

    public function __construct() {
        $this->taskRepo = new TaskRepository();
        $this->companyRepo = new CompanyRepository();
    }

    public function getAllTasks(): array {
        $tasks = $this->taskRepo->getAll();
        $responses = [];

        foreach ($tasks as $task) {
            foreach ($task->finished_by as $index => &$completion) {
                $completion['rank'] = $index + 1;
            }
            $responses[] = Task::fromModel($task);
        }
        return $responses;
    }

    /**
     * @throws Exception
     */
    public function completeTask(TaskCompleteRequest $request): TaskResponse {
        $task = $this->taskRepo->getById($request->task_id);
        if (!$task) {
            throw new Exception("Task not found", 404);
        }

        $company = $this->companyRepo->findById($request->company_id);
        if (!$company) {
            throw new Exception("Company not found", 404);
        }

        if ($this->taskRepo->hasAttempted($request)) {
            throw new Exception("Task already attempted by this company", 400);
        }

        // --- FAILURE PATH ---
        if ($request->success === false) {
            $penalty = $task->penalty;
            $amount = -abs($penalty); // Ensure negative

            $description = "Penalty of ƒ {$amount} for incorrect task submission: {$task->category} - {$task->name}";
            $this->taskRepo->completeTask($request, $amount, $description);

            return TaskResponse::CreateFromCompletion($company, $task, $amount, false);
        }

        // --- SUCCESS PATH ---
        $successfulCompletions = $this->taskRepo->countSuccessfulCompletions($request->task_id);
        $reward = $this->getRewardAmount($task, $successfulCompletions);
        $rankLabel = $this->getRankLabel($successfulCompletions);

        $description = "Company {$company->name} got ƒ $reward for being the $rankLabel to complete task {$task->category} - {$task->name}";
        $this->taskRepo->completeTask($request, $reward, $description);

        return TaskResponse::CreateFromCompletion($company, $task, $reward, true);
    }

    private function getRankLabel(int $rank): string {
        $rank ++;
        return $rank . ($rank === 1 ? 'ste' : 'de');
    }

    private function getRewardAmount(Task $task, int $existingCompletions): int {
        return match ($existingCompletions) {
            0 => $task->reward_p1,
            1 => $task->reward_p2,
            2 => $task->reward_p3,
            3 => $task->reward_p4,
            4 => $task->reward_p5,
            default => 0,
        };
    }
}
