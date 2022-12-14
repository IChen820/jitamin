<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../Base.php';

use Jitamin\Model\ProjectModel;
use Jitamin\Model\SettingModel;
use Jitamin\Model\TaskFinderModel;
use Jitamin\Model\TaskModel;
use Jitamin\Model\TaskTagModel;

class TaskModelTest extends Base
{
    public function onCreate($event)
    {
        $this->assertInstanceOf('Jitamin\Bus\Event\TaskEvent', $event);

        $event_data = $event->getAll();
        $this->assertNotEmpty($event_data);
        $this->assertEquals(1, $event_data['task_id']);
        $this->assertEquals('test', $event_data['task']['title']);
    }

    public function testNoTitle()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->container['dispatcher']->addListener(TaskModel::EVENT_CREATE_UPDATE, function () {
        });
        $this->container['dispatcher']->addListener(TaskModel::EVENT_CREATE, function () {
        });

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1]));

        $called = $this->container['dispatcher']->getCalledListeners();
        $this->assertArrayHasKey(TaskModel::EVENT_CREATE_UPDATE.'.closure', $called);
        $this->assertArrayHasKey(TaskModel::EVENT_CREATE.'.closure', $called);

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(1, $task['id']);
        $this->assertEquals('Untitled', $task['title']);
        $this->assertEquals(1, $task['project_id']);
    }

    public function testMinimum()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $finderModel = new TaskFinderModel($this->container);

        $this->container['dispatcher']->addListener(TaskModel::EVENT_CREATE_UPDATE, function () {
        });
        $this->container['dispatcher']->addListener(TaskModel::EVENT_CREATE, [$this, 'onCreate']);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));

        $called = $this->container['dispatcher']->getCalledListeners();
        $this->assertArrayHasKey(TaskModel::EVENT_CREATE_UPDATE.'.closure', $called);
        $this->assertArrayHasKey(TaskModel::EVENT_CREATE.'.TaskModelTest::onCreate', $called);

        $task = $finderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertNotFalse($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals('yellow', $task['color_id']);
        $this->assertEquals(1, $task['project_id']);
        $this->assertEquals(1, $task['column_id']);
        $this->assertEquals(0, $task['owner_id']);
        $this->assertEquals(0, $task['category_id']);
        $this->assertEquals(0, $task['creator_id']);

        $this->assertEquals('test', $task['title']);
        $this->assertEquals('', $task['description']);
        $this->assertEquals('', $task['reference']);

        $this->assertEquals(time(), $task['date_creation'], 'Wrong timestamp', 1);
        $this->assertEquals(time(), $task['date_modification'], 'Wrong timestamp', 1);
        $this->assertEquals(0, $task['date_due']);
        $this->assertEquals(0, $task['date_completed']);
        $this->assertEquals(0, $task['date_started']);

        $this->assertEquals(0, $task['time_estimated']);
        $this->assertEquals(0, $task['time_spent']);

        $this->assertEquals(1, $task['position']);
        $this->assertEquals(1, $task['is_active']);
        $this->assertEquals(0, $task['score']);
    }

    public function testColorId()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'color_id' => 'blue']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals('blue', $task['color_id']);
    }

    public function testOwnerId()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'owner_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals(1, $task['owner_id']);
    }

    public function testCategoryId()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'category_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals(1, $task['category_id']);
    }

    public function testCreatorId()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'creator_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals(1, $task['creator_id']);
    }

    public function testThatCreatorIsDefined()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->container['sessionStorage']->user = ['id' => 1];

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals(1, $task['creator_id']);
    }

    public function testColumnId()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'column_id' => 2]));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals(2, $task['column_id']);
        $this->assertEquals(1, $task['position']);
    }

    public function testPosition()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'column_id' => 2]));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals(2, $task['column_id']);
        $this->assertEquals(1, $task['position']);

        $this->assertEquals(2, $taskModel->create(['project_id' => 1, 'title' => 'test', 'column_id' => 2]));

        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);

        $this->assertEquals(2, $task['id']);
        $this->assertEquals(2, $task['column_id']);
        $this->assertEquals(2, $task['position']);
    }

    public function testDescription()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'description' => 'test']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals('test', $task['description']);
    }

    public function testReference()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'reference' => 'test']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);

        $this->assertEquals(1, $task['id']);
        $this->assertEquals('test', $task['reference']);
    }

    public function testDateDue()
    {
        $date = '2014-11-23';
        $timestamp = strtotime('+2days');
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_due' => $date]));
        $this->assertEquals(2, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_due' => $timestamp]));
        $this->assertEquals(3, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_due' => '']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(1, $task['id']);
        $this->assertEquals($date, date('Y-m-d', $task['date_due']));

        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['id']);
        $this->assertEquals(date('Y-m-d 00:00', $timestamp), date('Y-m-d 00:00', $task['date_due']));

        $task = $taskFinderModel->getById(3);
        $this->assertEquals(3, $task['id']);
        $this->assertEquals(0, $task['date_due']);
    }

    public function testDateStarted()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));

        // Set only a date
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_started' => '2014-11-24']));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('2014-11-24 '.date('H:i'), date('Y-m-d H:i', $task['date_started']));

        // Set a datetime
        $this->assertEquals(2, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_started' => '2014-11-24 16:25']));

        $task = $taskFinderModel->getById(2);
        $this->assertEquals('2014-11-24 16:25', date('Y-m-d H:i', $task['date_started']));

        // Set a datetime
        $this->assertEquals(3, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_started' => '11/24/2014 18:25']));

        $task = $taskFinderModel->getById(3);
        $this->assertEquals('2014-11-24 18:25', date('Y-m-d H:i', $task['date_started']));

        // Set a timestamp
        $this->assertEquals(4, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_started' => time()]));

        $task = $taskFinderModel->getById(4);
        $this->assertEquals(time(), $task['date_started'], '', 1);

        // Set empty string
        $this->assertEquals(5, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_started' => '']));
        $task = $taskFinderModel->getById(5);
        $this->assertEquals(0, $task['date_started']);
    }

    public function testTime()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'time_estimated' => 1.5, 'time_spent' => 2.3]));
        $this->assertEquals(2, $taskModel->create(['project_id' => 1, 'title' => 'test', 'time_estimated' => '', 'time_spent' => '']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(1, $task['id']);
        $this->assertEquals(1.5, $task['time_estimated']);
        $this->assertEquals(2.3, $task['time_spent']);

        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['id']);
        $this->assertEquals(0, $task['time_estimated']);
        $this->assertEquals(0, $task['time_spent']);
    }

    public function testStripColumn()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'another_task' => '1']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
    }

    public function testScore()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'score' => '3']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertNotFalse($task);
        $this->assertEquals(3, $task['score']);
    }

    public function testProgress()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'progress' => 10]));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertNotFalse($task);
        $this->assertEquals(10, $task['progress']);
    }

    public function testDefaultColor()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $settingModel = new SettingModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test1']));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals('yellow', $task['color_id']);

        $this->assertTrue($settingModel->save(['default_color' => 'orange']));
        $this->container['memoryCache']->flush();

        $this->assertEquals(2, $taskModel->create(['project_id' => 1, 'title' => 'test2']));

        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals('orange', $task['color_id']);
    }

    public function testDueDateYear2038TimestampBug()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test', 'date_due' => strtotime('2050-01-10 12:30')]));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals('2050-01-10 00:00', date('Y-m-d H:i', $task['date_due']));
    }

    public function onCreateUpdate($event)
    {
        $this->assertInstanceOf('Jitamin\Bus\Event\TaskEvent', $event);

        $event_data = $event->getAll();
        $this->assertNotEmpty($event_data);
        $this->assertEquals(1, $event_data['task_id']);
        $this->assertEquals('After', $event_data['task']['title']);
        $this->assertEquals('After', $event_data['changes']['title']);
    }

    public function onUpdate($event)
    {
        $this->assertInstanceOf('Jitamin\Bus\Event\TaskEvent', $event);

        $event_data = $event->getAll();
        $this->assertNotEmpty($event_data);
        $this->assertEquals(1, $event_data['task_id']);
        $this->assertEquals('After', $event_data['task']['title']);
    }

    public function onAssigneeChange($event)
    {
        $this->assertInstanceOf('Jitamin\Bus\Event\TaskEvent', $event);

        $event_data = $event->getAll();
        $this->assertNotEmpty($event_data);
        $this->assertEquals(1, $event_data['task_id']);
        $this->assertEquals(1, $event_data['changes']['owner_id']);
    }

    public function testThatNoEventAreFiredWhenNoChanges()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $this->container['dispatcher']->addListener(TaskModel::EVENT_CREATE_UPDATE, [$this, 'onCreateUpdate']);
        $this->container['dispatcher']->addListener(TaskModel::EVENT_UPDATE, [$this, 'onUpdate']);

        $this->assertTrue($taskModel->update(['id' => 1, 'title' => 'test']));

        $this->assertEmpty($this->container['dispatcher']->getCalledListeners());
    }

    public function testChangeTitle()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'Before', 'project_id' => 1]));

        $this->container['dispatcher']->addListener(TaskModel::EVENT_CREATE_UPDATE, [$this, 'onCreateUpdate']);
        $this->container['dispatcher']->addListener(TaskModel::EVENT_UPDATE, [$this, 'onUpdate']);

        $this->assertTrue($taskModel->update(['id' => 1, 'title' => 'After']));

        $called = $this->container['dispatcher']->getCalledListeners();
        $this->assertArrayHasKey(TaskModel::EVENT_CREATE_UPDATE.'.TaskModelTest::onCreateUpdate', $called);
        $this->assertArrayHasKey(TaskModel::EVENT_UPDATE.'.TaskModelTest::onUpdate', $called);

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('After', $task['title']);
    }

    public function testChangeAssignee()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(0, $task['owner_id']);

        $this->container['dispatcher']->addListener(TaskModel::EVENT_ASSIGNEE_CHANGE, [$this, 'onAssigneeChange']);

        $this->assertTrue($taskModel->update(['id' => 1, 'owner_id' => 1]));

        $called = $this->container['dispatcher']->getCalledListeners();
        $this->assertArrayHasKey(TaskModel::EVENT_ASSIGNEE_CHANGE.'.TaskModelTest::onAssigneeChange', $called);

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(1, $task['owner_id']);
    }

    public function testChangeDescription()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('', $task['description']);

        $this->assertTrue($taskModel->update(['id' => 1, 'description' => 'test']));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('test', $task['description']);
    }

    public function testChangeCategory()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(0, $task['category_id']);

        $this->assertTrue($taskModel->update(['id' => 1, 'category_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(1, $task['category_id']);
    }

    public function testChangeColor()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('yellow', $task['color_id']);

        $this->assertTrue($taskModel->update(['id' => 1, 'color_id' => 'blue']));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('blue', $task['color_id']);
    }

    public function testChangeScore()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(0, $task['score']);

        $this->assertTrue($taskModel->update(['id' => 1, 'score' => 13]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(13, $task['score']);
    }

    public function testChangeProgress()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(0, $task['progress']);

        $this->assertTrue($taskModel->update(['id' => 1, 'progress' => 20]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(20, $task['progress']);
    }

    public function testChangeDueDate()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(0, $task['date_due']);

        $this->assertTrue($taskModel->update(['id' => 1, 'date_due' => '2014-11-24']));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('2014-11-24', date('Y-m-d', $task['date_due']));

        $this->assertTrue($taskModel->update(['id' => 1, 'date_due' => time()]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(date('Y-m-d'), date('Y-m-d', $task['date_due']));
    }

    public function testChangeStartedDate()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(0, $task['date_started']);

        // Set only a date
        $this->assertTrue($taskModel->update(['id' => 1, 'date_started' => '2014-11-24']));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('2014-11-24 '.date('H:i'), date('Y-m-d H:i', $task['date_started']));

        // Set a datetime
        $this->assertTrue($taskModel->update(['id' => 1, 'date_started' => '2014-11-24 16:25']));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('2014-11-24 16:25', date('Y-m-d H:i', $task['date_started']));

        // Set a datetime
        $this->assertTrue($taskModel->update(['id' => 1, 'date_started' => '11/24/2014 18:25']));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals('2014-11-24 18:25', date('Y-m-d H:i', $task['date_started']));

        // Set a timestamp
        $this->assertTrue($taskModel->update(['id' => 1, 'date_started' => time()]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(time(), $task['date_started'], '', 1);
    }

    public function testChangeTimeEstimated()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(0, $task['time_estimated']);

        $this->assertTrue($taskModel->update(['id' => 1, 'time_estimated' => 13.3]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(13.3, $task['time_estimated']);
    }

    public function testChangeTimeSpent()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(0, $task['time_spent']);

        $this->assertTrue($taskModel->update(['id' => 1, 'time_spent' => 13.3]));

        $task = $taskFinderModel->getById(1);
        $this->assertEquals(13.3, $task['time_spent']);
    }

    public function testChangeTags()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskTagModel = new TaskTagModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'tags' => ['tag1', 'tag2']]));
        $this->assertTrue($taskModel->update(['id' => 1, 'tags' => ['tag2']]));

        $tags = $taskTagModel->getList(1);
        $this->assertEquals([2 => 'tag2'], $tags);
    }

    public function testRemoveAllTags()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskTagModel = new TaskTagModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['title' => 'test1', 'project_id' => 1, 'tags' => ['tag1', 'tag2']]));
        $this->assertEquals(2, $taskModel->create(['title' => 'test2', 'project_id' => 1, 'tags' => ['tag1', 'tag2']]));

        $this->assertTrue($taskModel->update(['id' => 1]));
        $tags = $taskTagModel->getList(1);
        $this->assertEquals([1 => 'tag1', 2 => 'tag2'], $tags);

        $this->assertTrue($taskModel->update(['id' => 1, 'tags' => []]));
        $tags = $taskTagModel->getList(1);
        $this->assertEquals([], $tags);

        $this->assertTrue($taskModel->update(['id' => 2, 'tags' => ['']]));
        $tags = $taskTagModel->getList(2);
        $this->assertEquals([], $tags);
    }

    public function testRemove()
    {
        $taskModel = new TaskModel($this->container);
        $taskModel = new TaskModel($this->container);
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'UnitTest']));
        $this->assertEquals(1, $taskModel->create(['title' => 'Task #1', 'project_id' => 1]));

        $this->assertTrue($taskModel->remove(1));
        $this->assertFalse($taskModel->remove(1234));
    }

    public function testGetTaskIdFromText()
    {
        $taskModel = new TaskModel($this->container);
        $this->assertEquals(123, $taskModel->getTaskIdFromText('My task #123'));
        $this->assertEquals(0, $taskModel->getTaskIdFromText('My task 123'));
    }
}
