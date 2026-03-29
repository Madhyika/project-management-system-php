<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Throwable;

final class ProjectController extends Controller
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

    public function index(): never
    {
        $this->render('projects/index', [
            'projects' => $this->projects->allWithTaskCounts(),
        ], 'Projects');
    }

    public function create(): never
    {
        $this->render('projects/form', [
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

    public function store(): never
    {
        verify_csrf();
        $validation = $this->validateProject($_POST);

        if ($validation['errors'] !== []) {
            set_errors($validation['errors']);
            set_old_input($_POST);
            $this->backTo('/projects/create');
        }

        try {
            $this->projects->create($validation['data']);
            flash('success', 'Project created successfully.');
            $this->redirect('/projects');
        } catch (Throwable $exception) {
            set_old_input($_POST);
            flash('danger', 'Failed to create project: ' . $exception->getMessage());
            $this->backTo('/projects/create');
        }
    }

    public function show(int $projectId): never
    {
        $statusFilter = $_GET['status'] ?? '';

        if (!in_array($statusFilter, ['', 'pending', 'in-progress', 'completed'], true)) {
            $statusFilter = '';
        }

        $project = $this->projects->findById($projectId);

        if ($project === null) {
            $this->notFound();
        }

        $this->render('projects/show', [
            'project' => $project,
            'tasks' => $this->tasks->forProject($projectId, $statusFilter),
            'stats' => $this->tasks->statsForProject($projectId),
            'statusFilter' => $statusFilter,
            'users' => $this->users->allWithAssignedTaskCounts(),
        ], $project['title']);
    }

    public function edit(int $projectId): never
    {
        $project = $this->projects->findById($projectId);

        if ($project === null) {
            $this->notFound();
        }

        $this->render('projects/form', [
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

    public function update(int $projectId): never
    {
        verify_csrf();

        if ($this->projects->findById($projectId) === null) {
            $this->notFound();
        }

        $validation = $this->validateProject($_POST);

        if ($validation['errors'] !== []) {
            set_errors($validation['errors']);
            set_old_input($_POST);
            $this->backTo('/projects/' . $projectId . '/edit');
        }

        try {
            $this->projects->updateById($projectId, $validation['data']);
            flash('success', 'Project updated successfully.');
            $this->redirect('/projects');
        } catch (Throwable $exception) {
            set_old_input($_POST);
            flash('danger', 'Failed to update project: ' . $exception->getMessage());
            $this->backTo('/projects/' . $projectId . '/edit');
        }
    }

    public function destroy(int $projectId): never
    {
        verify_csrf();

        if ($this->projects->findById($projectId) === null) {
            $this->notFound();
        }

        try {
            $this->projects->deleteById($projectId);
            flash('success', 'Project deleted successfully.');
        } catch (Throwable $exception) {
            flash('danger', 'Failed to delete project: ' . $exception->getMessage());
        }

        $this->redirect('/projects');
    }

    private function validateProject(array $input): array
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
}
