<?php

declare(strict_types=1);

function load_env(string $file): void
{
    if (!is_file($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
    }
}

function env(string $key, ?string $default = null): ?string
{
    return $_ENV[$key] ?? $default;
}

function app_name(): string
{
    return env('APP_NAME', 'Project Management System');
}

function base_url(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $directory = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    return $directory === '' || $directory === '.' ? '' : $directory;
}

function url(string $path = '/'): string
{
    $normalized = '/' . ltrim($path, '/');

    if ($normalized === '/index.php') {
        $normalized = '/';
    }

    return base_url() . ($normalized === '/' ? '/' : $normalized);
}

function request_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $baseUrl = base_url();

    if ($baseUrl !== '' && str_starts_with($uri, $baseUrl)) {
        $uri = substr($uri, strlen($baseUrl)) ?: '/';
    }

    return '/' . ltrim($uri, '/');
}

function request_method(): string
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'POST' && isset($_POST['_method'])) {
        return strtoupper((string) $_POST['_method']);
    }

    return $method;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function str_limit(?string $value, int $limit = 90): string
{
    $value = trim((string) $value);

    if (mb_strlen($value) <= $limit) {
        return $value;
    }

    return rtrim(mb_substr($value, 0, $limit - 3)) . '...';
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $connection = env('DB_CONNECTION', 'sqlite');

    if ($connection === 'mysql') {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $database = env('DB_DATABASE', '');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }

    $database = env('DB_DATABASE', BASE_PATH . '/database/database.sqlite');

    if (!str_starts_with($database, '/') && !preg_match('/^[A-Za-z]:[\\\\\\/]/', $database)) {
        $database = BASE_PATH . '/' . ltrim($database, '/');
    }

    $directory = dirname($database);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    if (!is_file($database)) {
        touch($database);
    }

    $pdo = new PDO('sqlite:' . $database, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function query_all(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

function query_one(string $sql, array $params = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    $result = $statement->fetch();

    return $result === false ? null : $result;
}

function execute_sql(string $sql, array $params = []): bool
{
    $statement = db()->prepare($sql);

    return $statement->execute($params);
}

function last_insert_id(): int
{
    return (int) db()->lastInsertId();
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        echo 'Invalid CSRF token.';
        exit;
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function consume_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $messages;
}

function set_errors(array $errors): void
{
    $_SESSION['errors'] = array_values($errors);
}

function consume_errors(): array
{
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);

    return $errors;
}

function set_old_input(array $input): void
{
    $_SESSION['old'] = $input;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['old'][$key] ?? $default;
}

function clear_old_input(): void
{
    unset($_SESSION['old']);
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function back_to(string $fallback): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $target = $referer !== '' ? $referer : url($fallback);

    header('Location: ' . $target);
    exit;
}

function render(string $view, array $data = [], ?string $title = null): never
{
    $errors = consume_errors();
    $flashMessages = consume_flash();
    $pageTitle = $title ?? app_name();

    extract($data, EXTR_SKIP);

    ob_start();
    require BASE_PATH . '/views/' . $view . '.php';
    $content = ob_get_clean();

    require BASE_PATH . '/views/layout.php';
    clear_old_input();
    exit;
}

function partial(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require BASE_PATH . '/views/' . $view . '.php';
}

function abort_not_found(): never
{
    http_response_code(404);
    render('errors/404', [], 'Page Not Found');
}

function validate_project(array $input): array
{
    $title = trim((string) ($input['title'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $startDate = (string) ($input['start_date'] ?? '');
    $endDate = (string) ($input['end_date'] ?? '');
    $status = (string) ($input['status'] ?? '');

    $errors = [];

    if ($title === '') {
        $errors[] = 'Project title is required.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Project title must be 255 characters or fewer.';
    }

    if ($description === '') {
        $errors[] = 'Project description is required.';
    }

    if (!validate_date($startDate)) {
        $errors[] = 'Start date must be a valid date.';
    }

    if (!validate_date($endDate)) {
        $errors[] = 'End date must be a valid date.';
    }

    if (validate_date($startDate) && validate_date($endDate) && $endDate < $startDate) {
        $errors[] = 'End date must be on or after the start date.';
    }

    if (!in_array($status, ['active', 'completed'], true)) {
        $errors[] = 'Project status must be active or completed.';
    }

    return [
        'errors' => $errors,
        'data' => [
            'title' => $title,
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
        ],
    ];
}

function validate_user(array $input): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $errors = [];

    if ($name === '') {
        $errors[] = 'User name is required.';
    } elseif (mb_strlen($name) > 255) {
        $errors[] = 'User name must be 255 characters or fewer.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (mb_strlen($email) > 255) {
        $errors[] = 'Email must be 255 characters or fewer.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email must be a valid email address.';
    } elseif (query_one('SELECT id FROM users WHERE email = :email', ['email' => $email]) !== null) {
        $errors[] = 'This email address is already in use.';
    }

    return [
        'errors' => $errors,
        'data' => [
            'name' => $name,
            'email' => $email,
        ],
    ];
}

function validate_task(array $input, int $projectId): array
{
    $title = trim((string) ($input['title'] ?? ''));
    $status = (string) ($input['status'] ?? '');
    $userId = (int) ($input['user_id'] ?? 0);
    $errors = [];

    if ($title === '') {
        $errors[] = 'Task title is required.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Task title must be 255 characters or fewer.';
    }

    if (!in_array($status, ['pending', 'in-progress', 'completed'], true)) {
        $errors[] = 'Task status must be pending, in-progress, or completed.';
    }

    if ($userId <= 0) {
        $errors[] = 'Please choose a user for the task.';
    } elseif (query_one('SELECT id FROM users WHERE id = :id', ['id' => $userId]) === null) {
        $errors[] = 'Selected user does not exist.';
    }

    return [
        'errors' => $errors,
        'data' => [
            'project_id' => $projectId,
            'title' => $title,
            'status' => $status,
            'user_id' => $userId,
        ],
    ];
}

function validate_date(string $value): bool
{
    if ($value === '') {
        return false;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);

    return $date instanceof DateTime && $date->format('Y-m-d') === $value;
}

function find_project(int $id): ?array
{
    return query_one('SELECT * FROM projects WHERE id = :id', ['id' => $id]);
}

function find_task(int $projectId, int $taskId): ?array
{
    return query_one(
        'SELECT * FROM tasks WHERE id = :id AND project_id = :project_id',
        ['id' => $taskId, 'project_id' => $projectId]
    );
}

function fetch_projects(): array
{
    return query_all(
        'SELECT p.*,
                COUNT(t.id) AS tasks_count,
                SUM(CASE WHEN t.status = "completed" THEN 1 ELSE 0 END) AS completed_tasks_count
         FROM projects p
         LEFT JOIN tasks t ON t.project_id = p.id
         GROUP BY p.id
         ORDER BY p.id DESC'
    );
}

function fetch_project_detail(int $projectId, string $statusFilter = ''): ?array
{
    $project = find_project($projectId);

    if ($project === null) {
        return null;
    }

    $taskSql = 'SELECT t.*, u.name AS assigned_user_name, u.email AS assigned_user_email
                FROM tasks t
                LEFT JOIN users u ON u.id = t.user_id
                WHERE t.project_id = :project_id';
    $params = ['project_id' => $projectId];

    if ($statusFilter !== '') {
        $taskSql .= ' AND t.status = :status';
        $params['status'] = $statusFilter;
    }

    $taskSql .= ' ORDER BY t.id DESC';
    $tasks = query_all($taskSql, $params);

    $stats = query_one(
        'SELECT COUNT(*) AS total,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = "in-progress" THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) AS completed
         FROM tasks
         WHERE project_id = :project_id',
        ['project_id' => $projectId]
    ) ?? ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];

    return [
        'project' => $project,
        'tasks' => $tasks,
        'stats' => array_map(static fn (mixed $value): int => (int) $value, $stats),
    ];
}

function fetch_users(): array
{
    return query_all(
        'SELECT u.*,
                COUNT(t.id) AS assigned_tasks_count
         FROM users u
         LEFT JOIN tasks t ON t.user_id = u.id
         GROUP BY u.id
         ORDER BY u.name ASC'
    );
}
