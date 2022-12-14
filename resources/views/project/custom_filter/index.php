<div class="page-header">
    <h2><?= t('Custom filters') ?></h2>
    <ul>
        <li>
            <i class="fa fa-plus fa-fw"></i>
            <?= $this->url->link(t('Add a custom filter'), 'Project/CustomFilterController', 'create', ['project_id' => $project['id']], false, 'popover') ?>
        </li>
    </ul>
</div>

<?php if (!empty($custom_filters)): ?>
<div>
    <table class="table-striped table-scrolling">
        <tr>
            <th class="column-15"><?= t('Name') ?></th>
            <th class="column-30"><?= t('Filter') ?></th>
            <th class="column-10"><?= t('Shared') ?></th>
            <th class="column-15"><?= t('Append/Replace') ?></th>
            <th class="column-25"><?= t('Owner') ?></th>
            <th class="column-5"><?= t('Actions') ?></th>
        </tr>
    <?php foreach ($custom_filters as $filter): ?>
         <tr>
            <td><?= $this->text->e($filter['name']) ?></td>
            <td><?= $this->text->e($filter['filter']) ?></td>
            <td>
            <?php if ($filter['is_shared'] == 1): ?>
                <?= t('Yes') ?>
            <?php else: ?>
                <?= t('No') ?>
            <?php endif ?>
            </td>
            <td>
            <?php if ($filter['append'] == 1): ?>
                <?= t('Append') ?>
            <?php else: ?>
                <?= t('Replace') ?>
            <?php endif ?>
            </td>
            <td><?= $this->text->e($filter['owner_name'] ?: $filter['owner_username']) ?></td>
            <td>
                <?php if ($filter['user_id'] == $this->user->getId() || $this->user->hasProjectAccess('Project/CustomFilterController', 'edit', $project['id'])): ?>
                    <div class="dropdown">
                    <a href="#" class="dropdown-menu dropdown-menu-link-icon"><i class="fa fa-cog fa-fw"></i><i class="fa fa-caret-down"></i></a>
                    <ul>
                        <li><?= $this->url->link(t('Remove'), 'Project/CustomFilterController', 'remove', ['project_id' => $filter['project_id'], 'filter_id' => $filter['id']], false, 'popover') ?></li>
                        <li><?= $this->url->link(t('Edit'), 'Project/CustomFilterController', 'edit', ['project_id' => $filter['project_id'], 'filter_id' => $filter['id']], false, 'popover') ?></li>
                    </ul>
                    </div>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach ?>
    </table>
</div>
<?php endif ?>
