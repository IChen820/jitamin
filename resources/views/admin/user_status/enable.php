<div class="page-header">
    <h2><?= t('Enable user') ?></h2>
</div>

<form action="<?= $this->url->href('Admin/UserStatusController', 'enable', ['user_id' => $user['id']]) ?>" method="post" autocomplete="off">
	<?= $this->form->csrf() ?>
	<div class="confirm">
	    <p class="alert alert-info"><?= t('Do you really want to enable this user: "%s"?', $user['name'] ?: $user['username']) ?></p>
	
	    <div class="form-actions">
	        <button type="submit" class="btn btn-danger"><?= t('Confirm') ?></button>
	        <?= t('or') ?>
	        <?= $this->url->link(t('cancel'), 'Admin/UserController', 'index', [], false, 'close-popover') ?>
	    </div>
	</div>
</form>
