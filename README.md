# Project Management System (Core PHP)

A simple project management system built with plain PHP, PDO, and MySQL or SQLite.
This version does not depend on Laravel at runtime.

## Features

- Create, update, and delete projects
- Create and manage tasks inside each project
- Assign tasks to users
- Filter tasks by status
- Session-based flash messages and validation errors
- Lightweight routing and rendering in Core PHP

## Tech Stack

- PHP 8.3+
- PDO
- SQLite or MySQL
- Plain PHP templates
- CSS served from `public/css/app.css`

## Project Structure

- `public/index.php`: front controller
- `src/`: app bootstrap, helpers, routing, validation, and database access
- `views/`: plain PHP templates
- `database/setup.php`: database/table setup and default user seeding

## Installation

1. Clone the repository:

```bash
git clone https://github.com/Madhyika/project-management-system-php.git
cd project-management-system-php
```

3. Copy environment settings:

```bash
cp .env.example .env
```

4. Configure the database:
    - For SQLite, keep `DB_CONNECTION=sqlite`. The app will use `database/database.sqlite` by default.

    - For MySQL, update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_management_system
DB_USERNAME=root
DB_PASSWORD=
```

5. Prepare the database:

## Usage

1. Ensure XAMPP is installed and Apache is running.

2. Place the project in XAMPP's htdocs directory (e.g., `/opt/lampp/htdocs/project-management-system`).

3. Open your browser and navigate to `http://localhost/project-management-system/public/`.

4. Log in with the default user (check `database/setup.php` for credentials).

5. Create projects, add tasks, assign users, and manage your projects.

## Contributing

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature-name`.
3. Commit your changes: `git commit -am 'Add some feature'`.
4. Push to the branch: `git push origin feature-name`.
5. Submit a pull request.
