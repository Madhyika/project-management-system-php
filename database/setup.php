<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$pdo = db();
$connection = env('DB_CONNECTION', 'sqlite');

if ($connection === 'mysql') {
    $queries = [
        'CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'CREATE TABLE IF NOT EXISTS projects (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM("active", "completed") NOT NULL DEFAULT "active"
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'CREATE TABLE IF NOT EXISTS tasks (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            project_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            status ENUM("pending", "in-progress", "completed") NOT NULL DEFAULT "pending",
            user_id BIGINT UNSIGNED NOT NULL,
            CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    ];
} else {
    $queries = [
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE
        )',
        'CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            start_date TEXT NOT NULL,
            end_date TEXT NOT NULL,
            status TEXT NOT NULL CHECK(status IN ("active", "completed"))
        )',
        'CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            status TEXT NOT NULL CHECK(status IN ("pending", "in-progress", "completed")),
            user_id INTEGER NOT NULL,
            FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE RESTRICT
        )',
    ];
}

foreach ($queries as $query) {
    $pdo->exec($query);
}

$existingUser = query_one('SELECT id FROM users LIMIT 1');

if ($existingUser === null) {
    $seedUsers = [
        ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
        ['name' => 'Brian Smith', 'email' => 'brian@example.com'],
        ['name' => 'Carla Gomez', 'email' => 'carla@example.com'],
    ];

    foreach ($seedUsers as $user) {
        execute_sql(
            'INSERT INTO users (name, email) VALUES (:name, :email)',
            $user
        );
    }
}

echo "Database is ready.\n";
