<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Throwable;

final class UserController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function index(): never
    {
        $this->render('users/index', [
            'users' => $this->users->allWithAssignedTaskCounts(),
        ], 'Users');
    }

    public function create(): never
    {
        $this->render('users/form', [
            'user' => [
                'name' => old('name', ''),
                'email' => old('email', ''),
            ],
        ], 'Add User');
    }

    public function store(): never
    {
        verify_csrf();
        $validation = $this->validateUser($_POST);

        if ($validation['errors'] !== []) {
            set_errors($validation['errors']);
            set_old_input($_POST);
            $this->backTo('/users/create');
        }

        try {
            $this->users->create($validation['data']);
            flash('success', 'User created successfully.');
            $this->redirect('/users');
        } catch (Throwable $exception) {
            set_old_input($_POST);
            flash('danger', 'Failed to create user: ' . $exception->getMessage());
            $this->backTo('/users/create');
        }
    }

    private function validateUser(array $input): array
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
        } elseif ($this->users->findByEmail($email) !== null) {
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
}
