<div class="page-header">
    <h2><?= t('Notifications') ?></h2>
</div>

<form method="post" action="<?= $this->url->href('Profile/ProfileController', 'notifications', ['user_id' => $user['id']]) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <h4><?= t('Notification methods:') ?></h4>
    <?= $this->form->checkboxes('notification_types', $types, $notifications) ?>

    <hr>
    <h4><?= t('I want to receive notifications for:') ?></h4>
    <?= $this->form->radios('notifications_filter', $filters, $notifications) ?>

    <hr>
    <?php if (!empty($projects)): ?>
        <h4><?= t('I want to receive notifications only for those projects:') ?></h4>
        <?= $this->form->checkboxes('notification_projects', $projects, $notifications) ?>
    <?php endif ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-success"><?= t('Save') ?></button>
        <?= t('or') ?>
        <?= $this->url->link(t('cancel'), 'Profile/ProfileController', 'show', ['user_id' => $user['id']]) ?>
    </div>
</form>
