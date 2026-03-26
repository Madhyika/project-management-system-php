<section class="panel narrow">
    <div class="panel-head">
        <h2><?= e($heading) ?></h2>
        <a class="button secondary" href="<?= e(url($backUrl)) ?>">Back</a>
    </div>

    <form method="post" action="<?= e(url($action)) ?>">
        <?= csrf_field() ?>
        <?php if (isset($methodOverride)): ?>
            <input type="hidden" name="_method" value="<?= e($methodOverride) ?>">
        <?php endif; ?>

        <div class="form-grid">
            <label>
                Title
                <input type="text" name="title" value="<?= e($project['title']) ?>" required maxlength="255">
            </label>

            <label class="full">
                Description
                <textarea name="description" rows="6" required><?= e($project['description']) ?></textarea>
            </label>

            <label>
                Start Date
                <input type="date" name="start_date" value="<?= e($project['start_date']) ?>" required>
            </label>

            <label>
                End Date
                <input type="date" name="end_date" value="<?= e($project['end_date']) ?>" required>
            </label>

            <label>
                Status
                <select name="status" required>
                    <?php foreach (['active', 'completed'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $project['status'] === $status ? 'selected' : '' ?>>
                            <?= e(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div></div>
        </div>

        <div class="action-row">
            <button class="button primary" type="submit"><?= e($submitLabel) ?></button>
            <a class="button secondary" href="<?= e(url($backUrl)) ?>">Cancel</a>
        </div>
    </form>
</section>
