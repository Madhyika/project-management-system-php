<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Throwable;

final class TaskController extends Controller
{
    private Project $projects;
    private Task $tasks;
    private User $users;

    public function __construct()
    {
        $this->projects = new Project();
        $this->tasks = new Task();
        $this->users = new User();
    }

    public function create(int $projectId): never
    {
        $project = $this->projects->findById($projectId);

        if ($project === null) {
            $this->notFound();
        }

        $this->render('tasks/form', [
            'heading' => 'Add Task',
            'submitLabel' => 'Create Task',
            'action' => '/projects/' . $project['id'] . '/tasks',
            'project' => $project,
            'task' => [
                'title' => old('title', ''),
                'status' => old('status', 'pending'),
                'user_id' => old('user_id', ''),
            ],
            'users' => $this->users->allWithAssignedTaskCounts(),
        ], 'Add Task');
    }

    public function store(int $projectId): never
    {
        verify_csrf();
        $project = $this->projects->findById($projectId);

        if ($project === null) {
            $this->notFound();
        }

        $validation = $this->validateTask($_POST, $projectId);

        if ($validation['errors'] !== []) {
            set_errors($validation['errors']);
            set_old_input($_POST);
            $this->backTo('/projects/' . $projectId . '/tasks/create');
        }

        try {
            $this->tasks->create($validation['data']);
            flash('success', 'Task created successfully.');
            $this->redirect('/projects/' . $projectId);
        } catch (Throwable $exception) {
            set_old_input($_POST);
            flash('danger', 'Failed to create task: ' . $exception->getMessage());
            $this->backTo('/projects/' . $projectId . '/tasks/create');
        }
    }

    public function edit(int $projectId, int $taskId): never
    {
        $project = $this->projects->findById($projectId);
        $task = $this->tasks->findByProjectAndId($projectId, $taskId);

        if ($project === null || $task === null) {
            $this->notFound();
        }

        $this->render('tasks/form', [
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
            'users' => $this->users->allWithAssignedTaskCounts(),
        ], 'Edit Task');
    }

    public function update(int $projectId, int $taskId): never
    {
        verify_csrf();

        if ($this->tasks->findByProjectAndId($projectId, $taskId) === null) {
            $this->notFound();
        }

        $validation = $this->validateTask($_POST, $projectId);

        if ($validation['errors'] !== []) {
            set_errors($validation['errors']);
            set_old_input($_POST);
            $this->backTo('/projects/' . $projectId . '/tasks/' . $taskId . '/edit');
        }

        try {
            $this->tasks->updateByProjectAndId($projectId, $taskId, $validation['data']);
            flash('success', 'Task updated successfully.');
            $this->redirect('/projects/' . $projectId);
        } catch (Throwable $exception) {
            set_old_input($_POST);
            flash('danger', 'Failed to update task: ' . $exception->getMessage());
            $this->backTo('/projects/' . $projectId . '/tasks/' . $taskId . '/edit');
        }
    }

    public function destroy(int $projectId, int $taskId): never
    {
        verify_csrf();

        if ($this->tasks->findByProjectAndId($projectId, $taskId) === null) {
            $this->notFound();
        }

        try {
            $this->tasks->deleteByProjectAndId($projectId, $taskId);
            flash('success', 'Task deleted successfully.');
        } catch (Throwable $exception) {
            flash('danger', 'Failed to delete task: ' . $exception->getMessage());
        }

        $this->redirect('/projects/' . $projectId);
    }

    private function validateTask(array $input, int $projectId): array
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
        } elseif ($this->users->findById($userId) === null) {
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
}
