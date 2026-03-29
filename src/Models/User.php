<?php

declare(strict_types=1);

namespace App\Models;

final class User extends Model
{
    public function allWithAssignedTaskCounts(): array
    {
        return $this->queryAll(
            'SELECT u.*,
                    COUNT(t.id) AS assigned_tasks_count
             FROM users u
             LEFT JOIN tasks t ON t.user_id = u.id
             GROUP BY u.id
             ORDER BY u.name ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE id = :id', ['id' => $id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne('SELECT id FROM users WHERE email = :email', ['email' => $email]);
    }

    public function create(array $data): bool
    {
        return $this->execute(
            'INSERT INTO users (name, email) VALUES (:name, :email)',
            $data
        );
    }
}
