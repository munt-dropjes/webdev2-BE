<?php

namespace Repositories;

use Exception;
use Models\DTO\TaskCompleteRequest;
use Models\Task;
use PDO;

class TaskRepository extends Repository
{
    public function getAll(): array {
        $sql = "SELECT
                    t.id as task_id,
                    t.name as task_name,
                    cat.label as category,
                    cat.reward_p1,
                    cat.reward_p2,
                    cat.reward_p3,
                    cat.reward_p4,
                    cat.reward_p5,
                    cat.penalty,
                    tc.company_id,
                    tc.completed_at,
                    co.name as company_name
                FROM tasks t
                JOIN task_categories cat ON t.category_id = cat.id
                LEFT JOIN task_completions tc ON t.id = tc.task_id
                LEFT JOIN companies co ON tc.company_id = co.id
                ORDER BY cat.id, t.id, tc.completed_at ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tasks = [];

        foreach ($rows as $row) {
            $taskId = $row['task_id'];

            if (!isset($tasks[$taskId])) {
                $task = new Task();
                $task->id = $taskId;
                $task->name = $row['task_name'];
                $task->category = $row['category'];
                $task->reward_p1 = $row['reward_p1'];
                $task->reward_p2 = $row['reward_p2'];
                $task->reward_p3 = $row['reward_p3'];
                $task->reward_p4 = $row['reward_p4'];
                $task->reward_p5 = $row['reward_p5'];
                $task->finished_by = [];

                $tasks[$taskId] = $task;
            }

            if ($row['company_id']) {
                $tasks[$taskId]->finished_by[] = [
                    'company_id' => (int)$row['company_id'],
                    'company_name' => $row['company_name'],
                    'completed_at' => $row['completed_at']
                ];
            }
        }
        return array_values($tasks);
    }

    public function getById(int $id): ?Task {
        $sql = "SELECT t.id, t.name, cat.label as category, cat.reward_p1, cat.reward_p2, cat.reward_p3, cat.reward_p4, cat.reward_p5
                FROM tasks t 
                JOIN task_categories cat ON t.category_id = cat.id 
                WHERE t.id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Task::class);
        return $stmt->fetch() ?: null;
    }

    public function countCompletions(int $taskId): int {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM task_completions WHERE task_id = ?");
        $stmt->execute([$taskId]);
        return (int) $stmt->fetchColumn();
    }

    public function hasCompleted(TaskCompleteRequest $request): bool {
        $stmt = $this->connection->prepare("SELECT id FROM task_completions WHERE task_id = ? AND company_id = ?");
        $stmt->execute([$request->task_id, $request->company_id]);
        return (bool) $stmt->fetch();
    }

    /**
     * @throws Exception
     */
    public function completeTask(TaskCompleteRequest $request, int $amount, string $description): void {
        try {
            $this->connection->beginTransaction();

            $this->connection->prepare("INSERT INTO task_completions (task_id, company_id) VALUES (?, ?)")
                ->execute([$request->task_id, $request->company_id]);

            $this->connection->prepare("UPDATE companies SET cash = cash + ? WHERE id = ?")
                ->execute([$amount, $request->company_id]);

            $this->connection->prepare("INSERT INTO transactions (company_id, amount, description) VALUES (?, ?, ?)")
                ->execute([$request->company_id, $amount, $description]);

            $this->connection->commit();
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            throw new Exception("Failed to complete task: " . $e->getMessage());
        }
    }
}
