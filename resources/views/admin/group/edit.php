<div class="page-header">
    <h2><?= t('Edit group') ?></h2>
</div>
<form class="popover-form" method="post" action="<?= $this->url->href('Admin/GroupController', 'update') ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <?= $this->form->hidden('id', $values) ?>
    <?= $this->form->hidden('external_id', $values) ?>

    <?= $this->form->label(t('Name'), 'name') ?>
    <?= $this->form->text('name', $values, $errors, ['autofocus', 'required', 'maxlength="100"']) ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-success"><?= t('Save') ?></button>
        <?= t('or') ?>
        <?= $this->url->link(t('cancel'), 'Admin/GroupController', 'index', [], false, 'close-popover') ?>
    </div>
</form>
