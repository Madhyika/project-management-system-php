<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(url('/css/app.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <header class="site-header">
        <div class="container shell">
            <div>
                <h1><?= e(app_name()) ?></h1>
                <p>Core PHP project and task tracking with user assignment.</p>
            </div>
            <nav>
                <a href="<?= e(url('/projects')) ?>">Projects</a>
                <a href="<?= e(url('/users')) ?>">Users</a>
            </nav>
        </div>
    </header>

    <main class="container page">
        <?= $content ?>
    </main>

    <?php
    $toastMessages = array_map(
        static function (array $message): array {
            $icon = match ($message['type']) {
                'success' => 'success',
                'danger' => 'error',
                'warning' => 'warning',
                default => 'info',
            };

            return [
                'icon' => $icon,
                'title' => ucfirst($message['type']),
                'text' => $message['message'],
            ];
        },
        $flashMessages
    );
    ?>
    <script>
        const validationErrors = <?= json_encode($errors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const flashMessages = <?= json_encode($toastMessages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

        if (validationErrors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation failed',
                html: `<ul style="text-align:left;padding-left:1.25rem;margin:0;">${validationErrors.map((error) => `<li>${error}</li>`).join('')}</ul>`,
                confirmButtonText: 'OK'
            });
        }

        flashMessages.forEach((message) => {
            Swal.fire({
                icon: message.icon,
                title: message.title,
                text: message.text,
                confirmButtonText: 'OK'
            });
        });
    </script>
</body>
</html>
