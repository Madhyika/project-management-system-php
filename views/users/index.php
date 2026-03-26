<section class="panel hero">
    <div>
        <h2>Users</h2>
        <p>Create users here so tasks can be assigned from the task forms.</p>
    </div>
    <a class="button primary" href="<?= e(url('/users/create')) ?>">Add User</a>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>User List</h3>
        <span class="muted"><?= count($users) ?> user(s)</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Assigned Tasks</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users === []): ?>
                    <tr>
                        <td colspan="3" class="muted">No users found yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= (int) $user['assigned_tasks_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
