<h2><?= $this->text->e($task['title']) ?> (#<?= $task['id'] ?>)</h2>

<p><?= t('New attachment added "%s"', $file['name']) ?></p>

<?= $this->render('notification/footer', ['task' => $task, 'application_url' => $application_url]) ?>
