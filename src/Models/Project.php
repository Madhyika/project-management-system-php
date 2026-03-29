<?php

declare(strict_types=1);

namespace App\Models;

final class Project extends Model
{
    public function allWithTaskCounts(): array
    {
        return $this->queryAll(
            'SELECT p.*,
                    COUNT(t.id) AS tasks_count,
                    SUM(CASE WHEN t.status = "completed" THEN 1 ELSE 0 END) AS completed_tasks_count
             FROM projects p
             LEFT JOIN tasks t ON t.project_id = p.id
             GROUP BY p.id
             ORDER BY p.id DESC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM projects WHERE id = :id', ['id' => $id]);
    }

    public function create(array $data): bool
    {
        return $this->execute(
            'INSERT INTO projects (title, description, start_date, end_date, status)
             VALUES (:title, :description, :start_date, :end_date, :status)',
            $data
        );
    }

    public function updateById(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE projects
             SET title = :title,
                 description = :description,
                 start_date = :start_date,
                 end_date = :end_date,
                 status = :status
             WHERE id = :id',
            $data + ['id' => $id]
        );
    }

    public function deleteById(int $id): bool
    {
        return $this->execute('DELETE FROM projects WHERE id = :id', ['id' => $id]);
    }
}
