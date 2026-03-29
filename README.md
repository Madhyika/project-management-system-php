# Project Management System (Core PHP)

A simple project management system built with plain PHP, PDO, and MySQL or SQLite.
It follows an OOP structure inspired by Laravel-style MVC ideas, but does not use Laravel or any other framework at runtime.

## Features

- Create, update, and delete projects
- Create and manage tasks inside each project
- Assign tasks to users
- Filter tasks by status
- Session-based flash messages and validation errors
- Lightweight controller-based routing in Core PHP
- Model classes for database operations

## Tech Stack

- PHP 8.3+
- PDO
- SQLite or MySQL
- Plain PHP templates
- CSS served from `public/css/app.css`

## Project Structure

- `public/index.php`: front controller
- `src/app.php`: route definitions
- `src/Core/`: bootstrap and router classes
- `src/Controllers/`: controller classes for request handling
- `src/Models/`: model classes for database operations
- `src/helpers.php`: shared helper functions for rendering, sessions, URLs, and utilities
- `views/`: plain PHP templates
- `database/setup.php`: database/table setup and default user seeding

## Architecture

The application now follows a simple OOP request flow:

1. A route is defined in `src/app.php`.
2. The router dispatches the request to a controller class and method.
3. The controller creates model objects and calls model methods.
4. The model handles database queries using PDO.
5. The controller returns a view or redirects the user.

Example:

- Route: `/projects/{project}`
- Controller: `ProjectController::show()`
- Model calls: `Project::findById()`, `Task::forProject()`, `Task::statsForProject()`

## Installation

1. Clone the repository:

```bash
git clone https://github.com/Madhyika/project-management-system-php.git
cd project-management-system-php
```

2. Copy environment settings:

```bash
cp .env.example .env
```

3. Configure the database.

For SQLite, keep `DB_CONNECTION=sqlite`. The app will use `database/database.sqlite` by default.

For MySQL, update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_management_system
DB_USERNAME=root
DB_PASSWORD=
```

4. Prepare the database:

```bash
php database/setup.php
```

## Usage

1. Ensure XAMPP is installed and Apache is running.

2. Place the project in XAMPP's htdocs directory (e.g., `/opt/lampp/htdocs/project-management-system`).

3. Open your browser and navigate to `http://localhost/project-management-system/public/`.

4. Create users, projects, and tasks from the browser interface.
