<section class="panel narrow">
    <div class="panel-head">
        <div>
            <h2><?= e($heading) ?></h2>
            <p class="muted">Project: <?= e($project['title']) ?></p>
        </div>
        <a class="button secondary" href="<?= e(url('/projects/' . $project['id'])) ?>">Back</a>
    </div>

    <form method="post" action="<?= e(url($action)) ?>">
        <?= csrf_field() ?>
        <?php if (isset($methodOverride)): ?>
            <input type="hidden" name="_method" value="<?= e($methodOverride) ?>">
        <?php endif; ?>

        <div class="form-grid">
            <label class="full">
                Title
                <input type="text" name="title" value="<?= e($task['title']) ?>" required maxlength="255">
            </label>

            <label>
                Status
                <select name="status" required>
                    <?php foreach (['pending', 'in-progress', 'completed'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $task['status'] === $status ? 'selected' : '' ?>>
                            <?= e(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Assign User
                <select name="user_id" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <?php $selected = (string) $task['user_id'] === (string) $user['id']; ?>
                        <option value="<?= (int) $user['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                            <?= e($user['name']) ?> (<?= e($user['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div></div>
        </div>

        <div class="action-row">
            <button class="button primary" type="submit"><?= e($submitLabel) ?></button>
            <a class="button secondary" href="<?= e(url('/projects/' . $project['id'])) ?>">Cancel</a>
        </div>
    </form>
</section>
