<?php

declare(strict_types=1);

$method = request_method();
$path = request_path();

if ($method === 'GET' && $path === '/') {
    redirect('/projects');
}

if ($method === 'GET' && $path === '/projects') {
    render('projects/index', [
        'projects' => fetch_projects(),
    ], 'Projects');
}

if ($method === 'GET' && $path === '/projects/create') {
    render('projects/form', [
        'heading' => 'Add Project',
        'submitLabel' => 'Create Project',
        'action' => '/projects',
        'project' => [
            'title' => old('title', ''),
            'description' => old('description', ''),
            'start_date' => old('start_date', ''),
            'end_date' => old('end_date', ''),
            'status' => old('status', 'active'),
        ],
        'backUrl' => '/projects',
    ], 'Add Project');
}

if ($method === 'POST' && $path === '/projects') {
    verify_csrf();
    $validation = validate_project($_POST);

    if ($validation['errors'] !== []) {
        set_errors($validation['errors']);
        set_old_input($_POST);
        back_to('/projects/create');
    }

    try {
        execute_sql(
            'INSERT INTO projects (title, description, start_date, end_date, status)
             VALUES (:title, :description, :start_date, :end_date, :status)',
            $validation['data']
        );

        flash('success', 'Project created successfully.');
        redirect('/projects');
    } catch (Throwable $exception) {
        set_old_input($_POST);
        flash('danger', 'Failed to create project: ' . $exception->getMessage());
        back_to('/projects/create');
    }
}

if ($method === 'GET' && preg_match('#^/projects/(\d+)$#', $path, $matches) === 1) {
    $projectId = (int) $matches[1];
    $statusFilter = $_GET['status'] ?? '';

    if (!in_array($statusFilter, ['', 'pending', 'in-progress', 'completed'], true)) {
        $statusFilter = '';
    }

    $detail = fetch_project_detail($projectId, $statusFilter);

    if ($detail === null) {
        abort_not_found();
    }

    render('projects/show', [
        'project' => $detail['project'],
        'tasks' => $detail['tasks'],
        'stats' => $detail['stats'],
        'statusFilter' => $statusFilter,
        'users' => fetch_users(),
    ], $detail['project']['title']);
}

if ($method === 'GET' && preg_match('#^/projects/(\d+)/edit$#', $path, $matches) === 1) {
    $project = find_project((int) $matches[1]);

    if ($project === null) {
        abort_not_found();
    }

    render('projects/form', [
        'heading' => 'Edit Project',
        'submitLabel' => 'Update Project',
        'action' => '/projects/' . $project['id'],
        'methodOverride' => 'PUT',
        'project' => [
            'title' => old('title', $project['title']),
            'description' => old('description', $project['description']),
            'start_date' => old('start_date', $project['start_date']),
            'end_date' => old('end_date', $project['end_date']),
            'status' => old('status', $project['status']),
        ],
        'backUrl' => '/projects',
    ], 'Edit Project');
}

if ($method === 'PUT' && preg_match('#^/projects/(\d+)$#', $path, $matches) === 1) {
    verify_csrf();
    $projectId = (int) $matches[1];

    if (find_project($projectId) === null) {
        abort_not_found();
    }

    $validation = validate_project($_POST);

    if ($validation['errors'] !== []) {
        set_errors($validation['errors']);
        set_old_input($_POST);
        back_to('/projects/' . $projectId . '/edit');
    }

    try {
        execute_sql(
            'UPDATE projects
             SET title = :title,
                 description = :description,
                 start_date = :start_date,
                 end_date = :end_date,
                 status = :status
             WHERE id = :id',
            $validation['data'] + ['id' => $projectId]
        );

        flash('success', 'Project updated successfully.');
        redirect('/projects');
    } catch (Throwable $exception) {
        set_old_input($_POST);
        flash('danger', 'Failed to update project: ' . $exception->getMessage());
        back_to('/projects/' . $projectId . '/edit');
    }
}

if ($method === 'DELETE' && preg_match('#^/projects/(\d+)$#', $path, $matches) === 1) {
    verify_csrf();
    $projectId = (int) $matches[1];

    if (find_project($projectId) === null) {
        abort_not_found();
    }

    try {
        execute_sql('DELETE FROM projects WHERE id = :id', ['id' => $projectId]);
        flash('success', 'Project deleted successfully.');
    } catch (Throwable $exception) {
        flash('danger', 'Failed to delete project: ' . $exception->getMessage());
    }

    redirect('/projects');
}

if ($method === 'GET' && preg_match('#^/projects/(\d+)/tasks/create$#', $path, $matches) === 1) {
    $project = find_project((int) $matches[1]);

    if ($project === null) {
        abort_not_found();
    }

    render('tasks/form', [
        'heading' => 'Add Task',
        'submitLabel' => 'Create Task',
        'action' => '/projects/' . $project['id'] . '/tasks',
        'project' => $project,
        'task' => [
            'title' => old('title', ''),
            'status' => old('status', 'pending'),
            'user_id' => old('user_id', ''),
        ],
        'users' => fetch_users(),
    ], 'Add Task');
}

if ($method === 'POST' && preg_match('#^/projects/(\d+)/tasks$#', $path, $matches) === 1) {
    verify_csrf();
    $projectId = (int) $matches[1];
    $project = find_project($projectId);

    if ($project === null) {
        abort_not_found();
    }

    $validation = validate_task($_POST, $projectId);

    if ($validation['errors'] !== []) {
        set_errors($validation['errors']);
        set_old_input($_POST);
        back_to('/projects/' . $projectId . '/tasks/create');
    }

    try {
        execute_sql(
            'INSERT INTO tasks (project_id, title, status, user_id)
             VALUES (:project_id, :title, :status, :user_id)',
            $validation['data']
        );

        flash('success', 'Task created successfully.');
        redirect('/projects/' . $projectId);
    } catch (Throwable $exception) {
        set_old_input($_POST);
        flash('danger', 'Failed to create task: ' . $exception->getMessage());
        back_to('/projects/' . $projectId . '/tasks/create');
    }
}

if ($method === 'GET' && preg_match('#^/projects/(\d+)/tasks/(\d+)/edit$#', $path, $matches) === 1) {
    $projectId = (int) $matches[1];
    $taskId = (int) $matches[2];
    $project = find_project($projectId);
    $task = find_task($projectId, $taskId);

    if ($project === null || $task === null) {
        abort_not_found();
    }

    render('tasks/form', [
        'heading' => 'Edit Task',
        'submitLabel' => 'Update Task',
        'action' => '/projects/' . $projectId . '/tasks/' . $taskId,
        'methodOverride' => 'PUT',
        'project' => $project,
        'task' => [
            'title' => old('title', $task['title']),
            'status' => old('status', $task['status']),
            'user_id' => old('user_id', (string) $task['user_id']),
        ],
        'users' => fetch_users(),
    ], 'Edit Task');
}

if ($method === 'PUT' && preg_match('#^/projects/(\d+)/tasks/(\d+)$#', $path, $matches) === 1) {
    verify_csrf();
    $projectId = (int) $matches[1];
    $taskId = (int) $matches[2];

    if (find_task($projectId, $taskId) === null) {
        abort_not_found();
    }

    $validation = validate_task($_POST, $projectId);

    if ($validation['errors'] !== []) {
        set_errors($validation['errors']);
        set_old_input($_POST);
        back_to('/projects/' . $projectId . '/tasks/' . $taskId . '/edit');
    }

    try {
        execute_sql(
            'UPDATE tasks
             SET title = :title,
                 status = :status,
                 user_id = :user_id
             WHERE id = :id AND project_id = :project_id',
            $validation['data'] + ['id' => $taskId]
        );

        flash('success', 'Task updated successfully.');
        redirect('/projects/' . $projectId);
    } catch (Throwable $exception) {
        set_old_input($_POST);
        flash('danger', 'Failed to update task: ' . $exception->getMessage());
        back_to('/projects/' . $projectId . '/tasks/' . $taskId . '/edit');
    }
}

if ($method === 'DELETE' && preg_match('#^/projects/(\d+)/tasks/(\d+)$#', $path, $matches) === 1) {
    verify_csrf();
    $projectId = (int) $matches[1];
    $taskId = (int) $matches[2];

    if (find_task($projectId, $taskId) === null) {
        abort_not_found();
    }

    try {
        execute_sql(
            'DELETE FROM tasks WHERE id = :id AND project_id = :project_id',
            ['id' => $taskId, 'project_id' => $projectId]
        );
        flash('success', 'Task deleted successfully.');
    } catch (Throwable $exception) {
        flash('danger', 'Failed to delete task: ' . $exception->getMessage());
    }

    redirect('/projects/' . $projectId);
}

if ($method === 'GET' && $path === '/users') {
    render('users/index', [
        'users' => fetch_users(),
    ], 'Users');
}

if ($method === 'GET' && $path === '/users/create') {
    render('users/form', [
        'user' => [
            'name' => old('name', ''),
            'email' => old('email', ''),
        ],
    ], 'Add User');
}

if ($method === 'POST' && $path === '/users') {
    verify_csrf();
    $validation = validate_user($_POST);

    if ($validation['errors'] !== []) {
        set_errors($validation['errors']);
        set_old_input($_POST);
        back_to('/users/create');
    }

    try {
        execute_sql(
            'INSERT INTO users (name, email) VALUES (:name, :email)',
            $validation['data']
        );

        flash('success', 'User created successfully.');
        redirect('/users');
    } catch (Throwable $exception) {
        set_old_input($_POST);
        flash('danger', 'Failed to create user: ' . $exception->getMessage());
        back_to('/users/create');
    }
}

abort_not_found();
