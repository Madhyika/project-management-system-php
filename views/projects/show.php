<section class="panel">
    <div class="hero">
        <div>
            <h2><?= e($project['title']) ?></h2>
            <p><?= e($project['description']) ?></p>
        </div>
        <div class="action-row">
            <a class="button secondary" href="<?= e(url('/projects')) ?>">Back</a>
            <a class="button secondary" href="<?= e(url('/projects/' . $project['id'] . '/edit')) ?>">Edit Project</a>
        </div>
    </div>

    <div class="meta-grid">
        <div class="stat">
            <span class="muted">Timeline</span>
            <strong><?= e($project['start_date']) ?> to <?= e($project['end_date']) ?></strong>
        </div>
        <div class="stat">
            <span class="muted">Status</span>
            <strong><span class="badge <?= e($project['status']) ?>"><?= e($project['status']) ?></span></strong>
        </div>
        <div class="stat">
            <span class="muted">Completed</span>
            <strong><?= (int) $stats['completed'] ?>/<?= (int) $stats['total'] ?></strong>
        </div>
    </div>
</section>

<section class="layout-grid">
    <div class="panel">
        <div class="panel-head">
            <h3>Tasks</h3>
            <a class="button primary" href="<?= e(url('/projects/' . $project['id'] . '/tasks/create')) ?>">Add Task</a>
        </div>

        <form method="get" class="filter-bar">
            <label>
                Filter Status
                <select name="status" onchange="this.form.submit()">
                    <?php foreach (['' => 'All', 'pending' => 'Pending', 'in-progress' => 'In Progress', 'completed' => 'Completed'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>

        <div class="meta-grid task-stats">
            <div class="stat">
                <span class="muted">Pending</span>
                <strong><?= (int) $stats['pending'] ?></strong>
            </div>
            <div class="stat">
                <span class="muted">In Progress</span>
                <strong><?= (int) $stats['in_progress'] ?></strong>
            </div>
            <div class="stat">
                <span class="muted">Completed</span>
                <strong><?= (int) $stats['completed'] ?></strong>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Assigned User</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tasks === []): ?>
                        <tr>
                            <td colspan="4" class="muted">No tasks found for this project or selected filter.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?= e($task['title']) ?></td>
                                <td><span class="badge <?= e($task['status']) ?>"><?= e($task['status']) ?></span></td>
                                <td>
                                    <?= e($task['assigned_user_name'] ?? 'Unassigned') ?>
                                    <?php if (!empty($task['assigned_user_email'])): ?>
                                        <p class="muted compact"><?= e($task['assigned_user_email']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-row">
                                        <a class="button secondary" href="<?= e(url('/projects/' . $project['id'] . '/tasks/' . $task['id'] . '/edit')) ?>">Edit</a>
                                        <form method="post" action="<?= e(url('/projects/' . $project['id'] . '/tasks/' . $task['id'])) ?>" class="inline-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button class="button danger" type="submit" onclick="return confirm('Delete this task?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <aside class="panel">
        <div class="panel-head">
            <h3>Users</h3>
            <div class="action-row">
                <span class="muted"><?= count($users) ?> available</span>
                <a class="button secondary" href="<?= e(url('/users/create')) ?>">Add User</a>
            </div>
        </div>

        <?php foreach ($users as $user): ?>
            <div class="user-card">
                <strong><?= e($user['name']) ?></strong>
                <p class="muted compact"><?= e($user['email']) ?></p>
                <span><?= (int) $user['assigned_tasks_count'] ?> assigned task(s)</span>
            </div>
        <?php endforeach; ?>
    </aside>
</section>
