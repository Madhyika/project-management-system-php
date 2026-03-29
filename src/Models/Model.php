<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

abstract class Model
{
    protected function db(): PDO
    {
        return db();
    }

    protected function queryAll(string $sql, array $params = []): array
    {
        $statement = $this->db()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        $statement = $this->db()->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetch();

        return $result === false ? null : $result;
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $statement = $this->db()->prepare($sql);

        return $statement->execute($params);
    }
}
