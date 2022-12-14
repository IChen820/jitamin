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
use Jitamin\Model\TaskModel;
use Jitamin\Model\TransitionModel;

class TransitionTest extends Base
{
    public function testSave()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $transitionModel = new TransitionModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));

        $task_event = [
            'project_id'    => 1,
            'task_id'       => 1,
            'src_column_id' => 1,
            'dst_column_id' => 2,
            'date_moved'    => time() - 3600,
        ];

        $this->assertTrue($transitionModel->save(1, $task_event));

        $transitions = $transitionModel->getAllByTask(1);
        $this->assertCount(1, $transitions);
        $this->assertEquals('Backlog', $transitions[0]['src_column']);
        $this->assertEquals('Ready', $transitions[0]['dst_column']);
        $this->assertEquals('', $transitions[0]['name']);
        $this->assertEquals('admin', $transitions[0]['username']);
        $this->assertEquals(1, $transitions[0]['user_id']);
        $this->assertEquals(time(), $transitions[0]['date'], '', 3);
        $this->assertEquals(3600, $transitions[0]['time_spent']);
    }

    public function testGetTimeSpentByTask()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $transitionModel = new TransitionModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));

        $task_event = [
            'project_id'    => 1,
            'task_id'       => 1,
            'src_column_id' => 1,
            'dst_column_id' => 2,
            'date_moved'    => time() - 3600,
        ];

        $this->assertTrue($transitionModel->save(1, $task_event));

        $task_event = [
            'project_id'    => 1,
            'task_id'       => 1,
            'src_column_id' => 2,
            'dst_column_id' => 3,
            'date_moved'    => time() - 1200,
        ];

        $this->assertTrue($transitionModel->save(1, $task_event));

        $expected = [
            '1' => 3600,
            '2' => 1200,
        ];

        $this->assertEquals($expected, $transitionModel->getTimeSpentByTask(1));
    }

    public function testGetAllByProject()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $transitionModel = new TransitionModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test1']));
        $this->assertEquals(2, $taskModel->create(['project_id' => 1, 'title' => 'test2']));

        $task_event = [
            'project_id'    => 1,
            'src_column_id' => 1,
            'dst_column_id' => 2,
            'date_moved'    => time() - 3600,
        ];

        $this->assertTrue($transitionModel->save(1, ['task_id' => 1] + $task_event));
        $this->assertTrue($transitionModel->save(1, ['task_id' => 2] + $task_event));

        $task_event = [
            'project_id'    => 1,
            'src_column_id' => 2,
            'dst_column_id' => 3,
            'date_moved'    => time() - 1200,
        ];

        $this->assertTrue($transitionModel->save(1, ['task_id' => 1] + $task_event));
        $this->assertTrue($transitionModel->save(1, ['task_id' => 2] + $task_event));

        $transitions = $transitionModel->getAllByProjectAndDate(1, date('Y-m-d'), date('Y-m-d'));
        $this->assertCount(4, $transitions);

        $this->assertEquals(2, $transitions[0]['id']);
        $this->assertEquals(1, $transitions[1]['id']);
        $this->assertEquals(2, $transitions[2]['id']);
        $this->assertEquals(1, $transitions[3]['id']);

        $this->assertEquals('test2', $transitions[0]['title']);
        $this->assertEquals('test1', $transitions[1]['title']);
        $this->assertEquals('test2', $transitions[2]['title']);
        $this->assertEquals('test1', $transitions[3]['title']);

        $this->assertEquals('Ready', $transitions[0]['src_column']);
        $this->assertEquals('Ready', $transitions[1]['src_column']);
        $this->assertEquals('Backlog', $transitions[2]['src_column']);
        $this->assertEquals('Backlog', $transitions[3]['src_column']);

        $this->assertEquals('Work in progress', $transitions[0]['dst_column']);
        $this->assertEquals('Work in progress', $transitions[1]['dst_column']);
        $this->assertEquals('Ready', $transitions[2]['dst_column']);
        $this->assertEquals('Ready', $transitions[3]['dst_column']);

        $this->assertEquals('admin', $transitions[0]['username']);
        $this->assertEquals('admin', $transitions[1]['username']);
        $this->assertEquals('admin', $transitions[2]['username']);
        $this->assertEquals('admin', $transitions[3]['username']);

        $this->assertEquals(1200, $transitions[0]['time_spent']);
        $this->assertEquals(1200, $transitions[1]['time_spent']);
        $this->assertEquals(3600, $transitions[2]['time_spent']);
        $this->assertEquals(3600, $transitions[3]['time_spent']);
    }
}
