<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(url('/css/app.css')) ?>">
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
        <?php if ($errors !== []): ?>
            <div class="alert alert-danger">
                <strong>Validation failed.</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php foreach ($flashMessages as $message): ?>
            <div class="alert alert-<?= e($message['type']) ?>">
                <?= e($message['message']) ?>
            </div>
        <?php endforeach; ?>

        <?= $content ?>
    </main>
</body>
</html>
