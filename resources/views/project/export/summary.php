<div class="page-header">
    <h2><?= t('Daily project summary export') ?></h2>
</div>

<p class="alert alert-info"><?= t('This export contains the number of tasks per column grouped per day.') ?></p>

<form method="get" action="?" autocomplete="off">
    <?= $this->form->hidden('controller', $values) ?>
    <?= $this->form->hidden('action', $values) ?>
    <?= $this->form->hidden('project_id', $values) ?>

    <?= $this->form->date(t('Start date'), 'from', $values) ?>
    <?= $this->form->date(t('End date'), 'to', $values) ?>

    <div class="form-help"><?= t('Others formats accepted: %s and %s', date('Y-m-d'), date('Y_m_d')) ?></div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success"><?= t('Execute') ?></button>
    </div>
</form>
