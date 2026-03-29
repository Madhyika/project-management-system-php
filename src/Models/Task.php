<?php

declare(strict_types=1);

namespace App\Models;

final class Task extends Model
{
    public function findByProjectAndId(int $projectId, int $taskId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM tasks WHERE id = :id AND project_id = :project_id',
            ['id' => $taskId, 'project_id' => $projectId]
        );
    }

    public function forProject(int $projectId, string $statusFilter = ''): array
    {
        $sql = 'SELECT t.*, u.name AS assigned_user_name, u.email AS assigned_user_email
                FROM tasks t
                LEFT JOIN users u ON u.id = t.user_id
                WHERE t.project_id = :project_id';
        $params = ['project_id' => $projectId];

        if ($statusFilter !== '') {
            $sql .= ' AND t.status = :status';
            $params['status'] = $statusFilter;
        }

        $sql .= ' ORDER BY t.id DESC';

        return $this->queryAll($sql, $params);
    }

    public function statsForProject(int $projectId): array
    {
        $stats = $this->queryOne(
            'SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = "in-progress" THEN 1 ELSE 0 END) AS in_progress,
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) AS completed
             FROM tasks
             WHERE project_id = :project_id',
            ['project_id' => $projectId]
        ) ?? ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];

        return array_map(static fn (mixed $value): int => (int) $value, $stats);
    }

    public function create(array $data): bool
    {
        return $this->execute(
            'INSERT INTO tasks (project_id, title, status, user_id)
             VALUES (:project_id, :title, :status, :user_id)',
            $data
        );
    }

    public function updateByProjectAndId(int $projectId, int $taskId, array $data): bool
    {
        return $this->execute(
            'UPDATE tasks
             SET title = :title,
                 status = :status,
                 user_id = :user_id
             WHERE id = :id AND project_id = :project_id',
            $data + ['id' => $taskId, 'project_id' => $projectId]
        );
    }

    public function deleteByProjectAndId(int $projectId, int $taskId): bool
    {
        return $this->execute(
            'DELETE FROM tasks WHERE id = :id AND project_id = :project_id',
            ['id' => $taskId, 'project_id' => $projectId]
        );
    }
}
