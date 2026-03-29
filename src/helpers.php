<?php

declare(strict_types=1);

function load_env(string $file): void
{
    (new App\Core\Environment($file))->load();
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
    } else {
        @chmod($directory, 0777);
    }

    if (!is_file($database)) {
        touch($database);
        @chmod($database, 0666);
    } else {
        @chmod($database, 0666);
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

function validate_date(string $value): bool
{
    if ($value === '') {
        return false;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);

    return $date instanceof DateTime && $date->format('Y-m-d') === $value;
}
