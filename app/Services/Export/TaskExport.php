<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jitamin\Export;

use Jitamin\Foundation\Base;
use Jitamin\Model\CategoryModel;
use Jitamin\Model\ColumnModel;
use Jitamin\Model\ProjectModel;
use Jitamin\Model\SwimlaneModel;
use Jitamin\Model\TaskModel;
use Jitamin\Model\UserModel;

/**
 * Task Export.
 */
class TaskExport extends Base
{
    /**
     * Fetch tasks and return the prepared CSV.
     *
     * @param int   $project_id Project id
     * @param mixed $from       Start date (timestamp or user formatted date)
     * @param mixed $to         End date (timestamp or user formatted date)
     *
     * @return array
     */
    public function export($project_id, $from, $to)
    {
        $tasks = $this->getTasks($project_id, $from, $to);
        $colors = $this->colorModel->getList();
        $defaultSwimlane = $this->swimlaneModel->getDefault($project_id);
        $results = [$this->getColumns()];

        foreach ($tasks as &$task) {
            $task = $this->format($task, $defaultSwimlane['default_swimlane'], $colors);
            $results[] = array_values($task);
        }

        return $results;
    }

    /**
     * Get the list of tasks for a given project and date range.
     *
     * @param int   $project_id Project id
     * @param mixed $from       Start date (timestamp or user formatted date)
     * @param mixed $to         End date (timestamp or user formatted date)
     *
     * @return array
     */
    protected function getTasks($project_id, $from, $to)
    {
        if (!is_numeric($from)) {
            $from = $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($from));
        }

        if (!is_numeric($to)) {
            $to = $this->dateParser->removeTimeFromTimestamp(strtotime('+1 day', $this->dateParser->getTimestamp($to)));
        }

        return $this->db->table(TaskModel::TABLE)
            ->columns(
                TaskModel::TABLE.'.id',
                TaskModel::TABLE.'.reference',
                ProjectModel::TABLE.'.name AS project_name',
                TaskModel::TABLE.'.is_active',
                CategoryModel::TABLE.'.name AS category_name',
                SwimlaneModel::TABLE.'.name AS swimlane_name',
                ColumnModel::TABLE.'.title AS column_title',
                TaskModel::TABLE.'.position',
                TaskModel::TABLE.'.color_id',
                TaskModel::TABLE.'.date_due',
                'uc.username AS creator_username',
                'uc.name AS creator_name',
                UserModel::TABLE.'.username AS assignee_username',
                UserModel::TABLE.'.name AS assignee_name',
                TaskModel::TABLE.'.score',
                TaskModel::TABLE.'.title',
                TaskModel::TABLE.'.date_creation',
                TaskModel::TABLE.'.date_modification',
                TaskModel::TABLE.'.date_completed',
                TaskModel::TABLE.'.date_started',
                TaskModel::TABLE.'.time_estimated',
                TaskModel::TABLE.'.time_spent'
            )
            ->join(UserModel::TABLE, 'id', 'owner_id', TaskModel::TABLE)
            ->left(UserModel::TABLE, 'uc', 'id', TaskModel::TABLE, 'creator_id')
            ->join(CategoryModel::TABLE, 'id', 'category_id', TaskModel::TABLE)
            ->join(ColumnModel::TABLE, 'id', 'column_id', TaskModel::TABLE)
            ->join(SwimlaneModel::TABLE, 'id', 'swimlane_id', TaskModel::TABLE)
            ->join(ProjectModel::TABLE, 'id', 'project_id', TaskModel::TABLE)
            ->gte(TaskModel::TABLE.'.date_creation', $from)
            ->lte(TaskModel::TABLE.'.date_creation', $to)
            ->eq(TaskModel::TABLE.'.project_id', $project_id)
            ->asc(TaskModel::TABLE.'.id')
            ->findAll();
    }

    /**
     * Format the output of a task array.
     *
     * @param array  $task
     * @param string $defaultSwimlaneName
     * @param array  $colors
     *
     * @return array
     */
    protected function format(array &$task, $defaultSwimlaneName, array $colors)
    {
        $task['is_active'] = $task['is_active'] == TaskModel::STATUS_OPEN ? l('Open') : l('Closed');
        $task['color_id'] = $colors[$task['color_id']];
        $task['score'] = $task['score'] ?: 0;
        $task['swimlane_name'] = $task['swimlane_name'] ?: $defaultSwimlaneName;

        $task = $this->dateParser->format(
            $task,
            ['date_due', 'date_modification', 'date_creation', 'date_started', 'date_completed'],
            $this->dateParser->getUserDateTimeFormat()
        );

        return $task;
    }

    /**
     * Get column titles.
     *
     * @return string[]
     */
    protected function getColumns()
    {
        return [
            l('Task Id'),
            l('Reference'),
            l('Project'),
            l('Status'),
            l('Category'),
            l('Swimlane'),
            l('Column'),
            l('Position'),
            l('Color'),
            l('Due date'),
            l('Creator'),
            l('Creator Name'),
            l('Assignee Username'),
            l('Assignee Name'),
            l('Complexity'),
            l('Title'),
            l('Creation date'),
            l('Modification date'),
            l('Completion date'),
            l('Start date'),
            l('Time estimated'),
            l('Time spent'),
        ];
    }
}
