<section class="panel narrow">
    <div class="panel-head">
        <h2>Add User</h2>
        <a class="button secondary" href="<?= e(url('/users')) ?>">Back</a>
    </div>

    <form method="post" action="<?= e(url('/users')) ?>">
        <?= csrf_field() ?>

        <div class="form-grid">
            <label>
                Name
                <input type="text" name="name" value="<?= e($user['name']) ?>" required maxlength="255">
            </label>

            <label>
                Email
                <input type="email" name="email" value="<?= e($user['email']) ?>" required maxlength="255">
            </label>
        </div>

        <div class="action-row">
            <button class="button primary" type="submit">Create User</button>
            <a class="button secondary" href="<?= e(url('/users')) ?>">Cancel</a>
        </div>
    </form>
</section>
