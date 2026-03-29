<?php

declare(strict_types=1);

use App\Controllers\ProjectController;
use App\Controllers\TaskController;
use App\Controllers\UserController;
use App\Controllers\HomeController;
use App\Core\Router;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/projects', [ProjectController::class, 'index']);
$router->get('/projects/create', [ProjectController::class, 'create']);
$router->post('/projects', [ProjectController::class, 'store']);
$router->get('/projects/{project}', [ProjectController::class, 'show']);
$router->get('/projects/{project}/edit', [ProjectController::class, 'edit']);
$router->put('/projects/{project}', [ProjectController::class, 'update']);
$router->delete('/projects/{project}', [ProjectController::class, 'destroy']);

$router->get('/projects/{project}/tasks/create', [TaskController::class, 'create']);
$router->post('/projects/{project}/tasks', [TaskController::class, 'store']);
$router->get('/projects/{project}/tasks/{task}/edit', [TaskController::class, 'edit']);
$router->put('/projects/{project}/tasks/{task}', [TaskController::class, 'update']);
$router->delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy']);

$router->get('/users', [UserController::class, 'index']);
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users', [UserController::class, 'store']);

$router->dispatch(request_method(), request_path());
