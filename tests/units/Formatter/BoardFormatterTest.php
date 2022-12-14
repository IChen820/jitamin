<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Jitamin\Formatter\BoardFormatter;
use Jitamin\Model\ColumnModel;
use Jitamin\Model\ProjectModel;
use Jitamin\Model\SwimlaneModel;
use Jitamin\Model\TaskFinderModel;
use Jitamin\Model\TaskModel;
use Jitamin\Model\TaskTagModel;

require_once __DIR__.'/../Base.php';

class BoardFormatterTest extends Base
{
    public function testFormatWithSwimlanes()
    {
        $projectModel = new ProjectModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $swimlaneModel->create(['name' => 'Swimlane 1', 'project_id' => 1]));
        $this->assertEquals(2, $swimlaneModel->create(['name' => 'Swimlane 2', 'project_id' => 1]));

        // 2 task within the same column but no score
        $this->assertEquals(1, $taskModel->create(['title' => 'Task 1', 'project_id' => 1, 'swimlane_id' => 0, 'column_id' => 1]));
        $this->assertEquals(2, $taskModel->create(['title' => 'Task 2', 'project_id' => 1, 'swimlane_id' => 0, 'column_id' => 1]));

        // 2 tasks in the same column with score
        $this->assertEquals(3, $taskModel->create(['title' => 'Task 3', 'project_id' => 1, 'swimlane_id' => 0, 'column_id' => 1, 'score' => 4]));
        $this->assertEquals(4, $taskModel->create(['title' => 'Task 4', 'project_id' => 1, 'swimlane_id' => 0, 'column_id' => 1, 'score' => 5]));

        // 1 task in 2nd column
        $this->assertEquals(5, $taskModel->create(['title' => 'Task 5', 'project_id' => 1, 'swimlane_id' => 0, 'column_id' => 2]));

        // tasks in same column but different swimlanes
        $this->assertEquals(6, $taskModel->create(['title' => 'Task 6', 'project_id' => 1, 'swimlane_id' => 0, 'column_id' => 3, 'score' => 1]));
        $this->assertEquals(7, $taskModel->create(['title' => 'Task 7', 'project_id' => 1, 'swimlane_id' => 1, 'column_id' => 3, 'score' => 2]));
        $this->assertEquals(8, $taskModel->create(['title' => 'Task 8', 'project_id' => 1, 'swimlane_id' => 2, 'column_id' => 3, 'score' => 3]));

        $board = BoardFormatter::getInstance($this->container)
            ->withQuery($taskFinderModel->getExtendedQuery())
            ->withProjectId(1)
            ->format();

        $this->assertCount(3, $board);

        $this->assertSame(0, $board[0]['id']);
        $this->assertEquals('Default swimlane', $board[0]['name']);
        $this->assertCount(4, $board[0]['columns']);
        $this->assertEquals(3, $board[0]['nb_swimlanes']);
        $this->assertEquals(4, $board[0]['nb_columns']);
        $this->assertEquals(6, $board[0]['nb_tasks']);
        $this->assertEquals(10, $board[0]['score']);
        $this->assertSame(1, $board[0]['columns'][0]['id']);
        $this->assertSame(2, $board[0]['columns'][1]['id']);
        $this->assertSame(3, $board[0]['columns'][2]['id']);
        $this->assertSame(4, $board[0]['columns'][3]['id']);

        $this->assertEquals(4, $board[0]['columns'][0]['column_nb_tasks']);
        $this->assertEquals(1, $board[0]['columns'][1]['column_nb_tasks']);
        $this->assertEquals(3, $board[0]['columns'][2]['column_nb_tasks']);
        $this->assertEquals(0, $board[0]['columns'][3]['column_nb_tasks']);

        $this->assertEquals(9, $board[0]['columns'][0]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][1]['column_score']);
        $this->assertEquals(6, $board[0]['columns'][2]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][3]['column_score']);

        $this->assertSame(9, $board[0]['columns'][0]['score']);
        $this->assertSame(0, $board[0]['columns'][1]['score']);
        $this->assertSame(1, $board[0]['columns'][2]['score']);
        $this->assertSame(0, $board[0]['columns'][3]['score']);

        $this->assertSame(4, $board[0]['columns'][0]['nb_tasks']);
        $this->assertSame(1, $board[0]['columns'][1]['nb_tasks']);
        $this->assertSame(1, $board[0]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[0]['columns'][3]['nb_tasks']);

        $this->assertEquals('Task 1', $board[0]['columns'][0]['tasks'][0]['title']);
        $this->assertEquals('Task 2', $board[0]['columns'][0]['tasks'][1]['title']);
        $this->assertEquals('Task 3', $board[0]['columns'][0]['tasks'][2]['title']);
        $this->assertEquals('Task 4', $board[0]['columns'][0]['tasks'][3]['title']);
        $this->assertEquals('Task 5', $board[0]['columns'][1]['tasks'][0]['title']);
        $this->assertEquals('Task 6', $board[0]['columns'][2]['tasks'][0]['title']);

        $this->assertSame(1, $board[1]['id']);
        $this->assertEquals('Swimlane 1', $board[1]['name']);
        $this->assertCount(4, $board[1]['columns']);
        $this->assertEquals(3, $board[1]['nb_swimlanes']);
        $this->assertEquals(4, $board[1]['nb_columns']);
        $this->assertEquals(1, $board[1]['nb_tasks']);
        $this->assertEquals(2, $board[1]['score']);

        $this->assertSame(0, $board[1]['columns'][0]['score']);
        $this->assertSame(0, $board[1]['columns'][1]['score']);
        $this->assertSame(2, $board[1]['columns'][2]['score']);
        $this->assertSame(0, $board[1]['columns'][3]['score']);

        $this->assertSame(0, $board[1]['columns'][0]['nb_tasks']);
        $this->assertSame(0, $board[1]['columns'][1]['nb_tasks']);
        $this->assertSame(1, $board[1]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[1]['columns'][3]['nb_tasks']);

        $this->assertEquals('Task 7', $board[1]['columns'][2]['tasks'][0]['title']);

        $this->assertEquals('Swimlane 2', $board[2]['name']);
        $this->assertCount(4, $board[2]['columns']);
        $this->assertEquals(3, $board[2]['nb_swimlanes']);
        $this->assertEquals(4, $board[2]['nb_columns']);
        $this->assertEquals(1, $board[2]['nb_tasks']);
        $this->assertEquals(3, $board[2]['score']);

        $this->assertSame(0, $board[2]['columns'][0]['score']);
        $this->assertSame(0, $board[2]['columns'][1]['score']);
        $this->assertSame(3, $board[2]['columns'][2]['score']);
        $this->assertSame(0, $board[2]['columns'][3]['score']);

        $this->assertSame(0, $board[2]['columns'][0]['nb_tasks']);
        $this->assertSame(0, $board[2]['columns'][1]['nb_tasks']);
        $this->assertSame(1, $board[2]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[2]['columns'][3]['nb_tasks']);

        $this->assertEquals('Task 8', $board[2]['columns'][2]['tasks'][0]['title']);
        $this->assertArrayHasKey('is_draggable', $board[2]['columns'][2]['tasks'][0]);
    }

    public function testFormatWithoutDefaultSwimlane()
    {
        $projectModel = new ProjectModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertTrue($swimlaneModel->disableDefault(1));
        $this->assertEquals(1, $swimlaneModel->create(['name' => 'Swimlane 1', 'project_id' => 1]));
        $this->assertEquals(2, $swimlaneModel->create(['name' => 'Swimlane 2', 'project_id' => 1]));

        $this->assertEquals(1, $taskModel->create(['title' => 'Task 1', 'project_id' => 1, 'swimlane_id' => 1, 'column_id' => 1]));
        $this->assertEquals(2, $taskModel->create(['title' => 'Task 2', 'project_id' => 1, 'swimlane_id' => 2, 'column_id' => 2]));
        $this->assertEquals(3, $taskModel->create(['title' => 'Task 3', 'project_id' => 1, 'swimlane_id' => 1, 'column_id' => 2, 'score' => 1]));
        $this->assertEquals(4, $taskModel->create(['title' => 'Task 4', 'project_id' => 1, 'swimlane_id' => 2, 'column_id' => 1]));

        $board = BoardFormatter::getInstance($this->container)
            ->withQuery($taskFinderModel->getExtendedQuery())
            ->withProjectId(1)
            ->format();

        $this->assertCount(2, $board);

        $this->assertEquals('Swimlane 1', $board[0]['name']);
        $this->assertCount(4, $board[0]['columns']);
        $this->assertEquals(2, $board[0]['nb_swimlanes']);
        $this->assertEquals(4, $board[0]['nb_columns']);
        $this->assertEquals(2, $board[0]['nb_tasks']);
        $this->assertEquals(1, $board[0]['score']);

        $this->assertEquals(2, $board[0]['columns'][0]['column_nb_tasks']);
        $this->assertEquals(2, $board[0]['columns'][1]['column_nb_tasks']);
        $this->assertEquals(0, $board[0]['columns'][2]['column_nb_tasks']);
        $this->assertEquals(0, $board[0]['columns'][3]['column_nb_tasks']);

        $this->assertEquals(0, $board[0]['columns'][0]['column_score']);
        $this->assertEquals(1, $board[0]['columns'][1]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][2]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][3]['column_score']);

        $this->assertSame(0, $board[0]['columns'][0]['score']);
        $this->assertSame(1, $board[0]['columns'][1]['score']);
        $this->assertSame(0, $board[0]['columns'][2]['score']);
        $this->assertSame(0, $board[0]['columns'][3]['score']);

        $this->assertSame(1, $board[0]['columns'][0]['nb_tasks']);
        $this->assertSame(1, $board[0]['columns'][1]['nb_tasks']);
        $this->assertSame(0, $board[0]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[0]['columns'][3]['nb_tasks']);

        $this->assertEquals('Task 1', $board[0]['columns'][0]['tasks'][0]['title']);
        $this->assertEquals('Task 3', $board[0]['columns'][1]['tasks'][0]['title']);

        $this->assertEquals('Swimlane 2', $board[1]['name']);
        $this->assertCount(4, $board[1]['columns']);
        $this->assertEquals(2, $board[1]['nb_swimlanes']);
        $this->assertEquals(4, $board[1]['nb_columns']);
        $this->assertEquals(2, $board[1]['nb_tasks']);
        $this->assertEquals(0, $board[1]['score']);

        $this->assertSame(0, $board[1]['columns'][0]['score']);
        $this->assertSame(0, $board[1]['columns'][1]['score']);
        $this->assertSame(0, $board[1]['columns'][2]['score']);
        $this->assertSame(0, $board[1]['columns'][3]['score']);

        $this->assertSame(1, $board[1]['columns'][0]['nb_tasks']);
        $this->assertSame(1, $board[1]['columns'][1]['nb_tasks']);
        $this->assertSame(0, $board[1]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[1]['columns'][3]['nb_tasks']);

        $this->assertEquals('Task 4', $board[1]['columns'][0]['tasks'][0]['title']);
        $this->assertEquals('Task 2', $board[1]['columns'][1]['tasks'][0]['title']);
    }

    public function testFormatWithoutSwimlane()
    {
        $projectModel = new ProjectModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertTrue($swimlaneModel->disableDefault(1));

        $board = BoardFormatter::getInstance($this->container)
            ->withQuery($taskFinderModel->getExtendedQuery())
            ->withProjectId(1)
            ->format();

        $this->assertCount(0, $board);
    }

    public function testFormatWithoutColumn()
    {
        $projectModel = new ProjectModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $columnModel = new ColumnModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertTrue($columnModel->remove(1));
        $this->assertTrue($columnModel->remove(2));
        $this->assertTrue($columnModel->remove(3));
        $this->assertTrue($columnModel->remove(4));

        $board = BoardFormatter::getInstance($this->container)
            ->withQuery($taskFinderModel->getExtendedQuery())
            ->withProjectId(1)
            ->format();

        $this->assertCount(0, $board);
    }

    public function testFormatWithoutTask()
    {
        $projectModel = new ProjectModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $swimlaneModel->create(['name' => 'Swimlane 1', 'project_id' => 1]));
        $this->assertEquals(2, $swimlaneModel->create(['name' => 'Swimlane 2', 'project_id' => 1]));

        $board = BoardFormatter::getInstance($this->container)
            ->withQuery($taskFinderModel->getExtendedQuery())
            ->withProjectId(1)
            ->format();

        $this->assertCount(3, $board);

        $this->assertEquals('Default swimlane', $board[0]['name']);
        $this->assertCount(4, $board[0]['columns']);
        $this->assertEquals(3, $board[0]['nb_swimlanes']);
        $this->assertEquals(4, $board[0]['nb_columns']);
        $this->assertEquals(0, $board[0]['nb_tasks']);
        $this->assertEquals(0, $board[0]['score']);

        $this->assertEquals(0, $board[0]['columns'][0]['column_nb_tasks']);
        $this->assertEquals(0, $board[0]['columns'][1]['column_nb_tasks']);
        $this->assertEquals(0, $board[0]['columns'][2]['column_nb_tasks']);
        $this->assertEquals(0, $board[0]['columns'][3]['column_nb_tasks']);

        $this->assertEquals(0, $board[0]['columns'][0]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][1]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][2]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][3]['column_score']);

        $this->assertSame(0, $board[0]['columns'][0]['score']);
        $this->assertSame(0, $board[0]['columns'][1]['score']);
        $this->assertSame(0, $board[0]['columns'][2]['score']);
        $this->assertSame(0, $board[0]['columns'][3]['score']);

        $this->assertSame(0, $board[0]['columns'][0]['nb_tasks']);
        $this->assertSame(0, $board[0]['columns'][1]['nb_tasks']);
        $this->assertSame(0, $board[0]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[0]['columns'][3]['nb_tasks']);

        $this->assertEquals('Swimlane 1', $board[1]['name']);
        $this->assertCount(4, $board[1]['columns']);
        $this->assertEquals(3, $board[1]['nb_swimlanes']);
        $this->assertEquals(4, $board[1]['nb_columns']);
        $this->assertEquals(0, $board[1]['nb_tasks']);
        $this->assertEquals(0, $board[1]['score']);

        $this->assertSame(0, $board[1]['columns'][0]['score']);
        $this->assertSame(0, $board[1]['columns'][1]['score']);
        $this->assertSame(0, $board[1]['columns'][2]['score']);
        $this->assertSame(0, $board[1]['columns'][3]['score']);

        $this->assertSame(0, $board[1]['columns'][0]['nb_tasks']);
        $this->assertSame(0, $board[1]['columns'][1]['nb_tasks']);
        $this->assertSame(0, $board[1]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[1]['columns'][3]['nb_tasks']);

        $this->assertEquals('Swimlane 2', $board[2]['name']);
        $this->assertCount(4, $board[2]['columns']);
        $this->assertEquals(3, $board[2]['nb_swimlanes']);
        $this->assertEquals(4, $board[2]['nb_columns']);
        $this->assertEquals(0, $board[2]['nb_tasks']);
        $this->assertEquals(0, $board[2]['score']);

        $this->assertSame(0, $board[2]['columns'][0]['score']);
        $this->assertSame(0, $board[2]['columns'][1]['score']);
        $this->assertSame(0, $board[2]['columns'][2]['score']);
        $this->assertSame(0, $board[2]['columns'][3]['score']);

        $this->assertSame(0, $board[2]['columns'][0]['nb_tasks']);
        $this->assertSame(0, $board[2]['columns'][1]['nb_tasks']);
        $this->assertSame(0, $board[2]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[2]['columns'][3]['nb_tasks']);
    }

    public function testFormatWithTags()
    {
        $projectModel = new ProjectModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskTagModel = new TaskTagModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test1']));
        $this->assertEquals(2, $taskModel->create(['project_id' => 1, 'title' => 'test2', 'column_id' => 3]));
        $this->assertEquals(3, $taskModel->create(['project_id' => 1, 'title' => 'test3']));

        $this->assertTrue($taskTagModel->save(1, 1, ['My tag 1', 'My tag 2']));
        $this->assertTrue($taskTagModel->save(1, 2, ['My tag 3']));

        $board = BoardFormatter::getInstance($this->container)
            ->withQuery($taskFinderModel->getExtendedQuery())
            ->withProjectId(1)
            ->format();

        $this->assertCount(1, $board);

        $this->assertEquals('Default swimlane', $board[0]['name']);
        $this->assertCount(4, $board[0]['columns']);
        $this->assertEquals(1, $board[0]['nb_swimlanes']);
        $this->assertEquals(4, $board[0]['nb_columns']);
        $this->assertEquals(3, $board[0]['nb_tasks']);
        $this->assertEquals(0, $board[0]['score']);

        $this->assertEquals(2, $board[0]['columns'][0]['column_nb_tasks']);
        $this->assertEquals(0, $board[0]['columns'][1]['column_nb_tasks']);
        $this->assertEquals(1, $board[0]['columns'][2]['column_nb_tasks']);
        $this->assertEquals(0, $board[0]['columns'][3]['column_nb_tasks']);

        $this->assertEquals(0, $board[0]['columns'][0]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][1]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][2]['column_score']);
        $this->assertEquals(0, $board[0]['columns'][3]['column_score']);

        $this->assertSame(0, $board[0]['columns'][0]['score']);
        $this->assertSame(0, $board[0]['columns'][1]['score']);
        $this->assertSame(0, $board[0]['columns'][2]['score']);
        $this->assertSame(0, $board[0]['columns'][3]['score']);

        $this->assertSame(2, $board[0]['columns'][0]['nb_tasks']);
        $this->assertSame(0, $board[0]['columns'][1]['nb_tasks']);
        $this->assertSame(1, $board[0]['columns'][2]['nb_tasks']);
        $this->assertSame(0, $board[0]['columns'][3]['nb_tasks']);

        $this->assertEquals('test1', $board[0]['columns'][0]['tasks'][0]['title']);
        $this->assertEquals('test3', $board[0]['columns'][0]['tasks'][1]['title']);
        $this->assertEquals('test2', $board[0]['columns'][2]['tasks'][0]['title']);

        $expected = [
            [
                'id'      => 1,
                'name'    => 'My tag 1',
                'task_id' => 1,
            ],
            [
                'id'      => 2,
                'name'    => 'My tag 2',
                'task_id' => 1,
            ],
        ];

        $this->assertEquals($expected, $board[0]['columns'][0]['tasks'][0]['tags']);
        $this->assertEquals([], $board[0]['columns'][0]['tasks'][1]['tags']);

        $expected = [
            [
                'id'      => 3,
                'name'    => 'My tag 3',
                'task_id' => 2,
            ],
        ];

        $this->assertEquals($expected, $board[0]['columns'][2]['tasks'][0]['tags']);
    }
}
