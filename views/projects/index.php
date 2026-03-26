<section class="panel hero">
    <div>
        <h2>Projects</h2>
        <p>Manage project schedules, tasks, and assigned users from a single dashboard.</p>
    </div>
    <a class="button primary" href="<?= e(url('/projects/create')) ?>">Add Project</a>
</section>

<section class="panel">
    <div class="panel-head">
        <h3>Project List</h3>
        <span class="muted"><?= count($projects) ?> project(s)</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Tasks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($projects === []): ?>
                    <tr>
                        <td colspan="5" class="muted">No projects found yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <strong><?= e($project['title']) ?></strong>
                                <p class="muted compact"><?= e(str_limit($project['description'], 90)) ?></p>
                            </td>
                            <td><?= e($project['start_date']) ?> to <?= e($project['end_date']) ?></td>
                            <td><span class="badge <?= e($project['status']) ?>"><?= e($project['status']) ?></span></td>
                            <td><?= (int) $project['completed_tasks_count'] ?>/<?= (int) $project['tasks_count'] ?> completed</td>
                            <td>
                                <div class="action-row">
                                    <a class="button secondary" href="<?= e(url('/projects/' . $project['id'])) ?>">View</a>
                                    <a class="button secondary" href="<?= e(url('/projects/' . $project['id'] . '/edit')) ?>">Edit</a>
                                    <form method="post" action="<?= e(url('/projects/' . $project['id'])) ?>" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="button danger" type="submit" onclick="return confirm('Delete this project and its tasks?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
