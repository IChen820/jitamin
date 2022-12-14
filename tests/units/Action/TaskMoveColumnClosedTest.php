<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Jitamin\Action\TaskMoveColumnClosed;
use Jitamin\Bus\EventBuilder\TaskEventBuilder;
use Jitamin\Model\ProjectModel;
use Jitamin\Model\TaskFinderModel;
use Jitamin\Model\TaskModel;

require_once __DIR__.'/../Base.php';

class TaskMoveColumnClosedTest extends Base
{
    public function testSuccess()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'is_active' => 0]));

        $event = TaskEventBuilder::getInstance($this->container)
            ->withTaskId(1)
            ->buildEvent();

        $action = new TaskMoveColumnClosed($this->container);
        $action->setProjectId(1);
        $action->setParam('dest_column_id', 2);

        $this->assertTrue($action->execute($event, TaskModel::EVENT_CLOSE));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals('test', $task['title']);
        $this->assertEquals(2, $task['column_id']);
    }

    public function testWhenTaskIsOpen()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));

        $event = TaskEventBuilder::getInstance($this->container)
            ->withTaskId(1)
            ->buildEvent();

        $action = new TaskMoveColumnClosed($this->container);
        $action->setProjectId(1);
        $action->setParam('dest_column_id', 2);

        $this->assertFalse($action->execute($event, TaskModel::EVENT_CLOSE));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals('test', $task['title']);
        $this->assertEquals(1, $task['column_id']);
    }

    public function testWhenTaskIsAlreadyInDestinationColumn()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'is_active' => 0, 'column_id' => 2]));

        $event = TaskEventBuilder::getInstance($this->container)
            ->withTaskId(1)
            ->buildEvent();

        $action = new TaskMoveColumnClosed($this->container);
        $action->setProjectId(1);
        $action->setParam('dest_column_id', 2);

        $this->assertFalse($action->execute($event, TaskModel::EVENT_CLOSE));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals('test', $task['title']);
        $this->assertEquals(2, $task['column_id']);
    }
}
