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

use Jitamin\Foundation\Security\Role;
use Jitamin\Model\CategoryModel;
use Jitamin\Model\ProjectModel;
use Jitamin\Model\ProjectUserRoleModel;
use Jitamin\Model\SwimlaneModel;
use Jitamin\Model\TagModel;
use Jitamin\Model\TaskFinderModel;
use Jitamin\Model\TaskModel;
use Jitamin\Model\TaskProjectDuplicationModel;
use Jitamin\Model\TaskTagModel;
use Jitamin\Model\UserModel;

class TaskProjectDuplicationModelTest extends Base
{
    public function testDuplicateAnotherProject()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $categoryModel = new CategoryModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        $this->assertNotFalse($categoryModel->create(['name' => 'Category #1', 'project_id' => 1]));
        $this->assertTrue($categoryModel->exists(1));

        $this->assertEquals(1, $taskModel->create([
            'title'       => 'test',
            'project_id'  => 1,
            'column_id'   => 2,
            'owner_id'    => 1,
            'category_id' => 1,
            'priority'    => 3,
        ]));

        $this->container['dispatcher']->addListener(TaskModel::EVENT_CREATE_UPDATE, function () {
        });
        $this->container['dispatcher']->addListener(TaskModel::EVENT_CREATE, function () {
        });

        // We duplicate our task to the 2nd project
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2));

        $called = $this->container['dispatcher']->getCalledListeners();
        $this->assertArrayHasKey(TaskModel::EVENT_CREATE_UPDATE.'.closure', $called);
        $this->assertArrayHasKey(TaskModel::EVENT_CREATE.'.closure', $called);

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(0, $task['owner_id']);
        $this->assertEquals(0, $task['category_id']);
        $this->assertEquals(0, $task['swimlane_id']);
        $this->assertEquals(6, $task['column_id']);
        $this->assertEquals(1, $task['position']);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals(3, $task['priority']);
        $this->assertEquals('test', $task['title']);
    }

    public function testDuplicateAnotherProjectWithCategory()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $categoryModel = new CategoryModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        $this->assertNotFalse($categoryModel->create(['name' => 'Category #1', 'project_id' => 1]));
        $this->assertNotFalse($categoryModel->create(['name' => 'Category #1', 'project_id' => 2]));
        $this->assertTrue($categoryModel->exists(1));
        $this->assertTrue($categoryModel->exists(2));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 2, 'category_id' => 1]));

        // We duplicate our task to the 2nd project
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(0, $task['owner_id']);
        $this->assertEquals(2, $task['category_id']);
        $this->assertEquals(0, $task['swimlane_id']);
        $this->assertEquals(6, $task['column_id']);
        $this->assertEquals(1, $task['position']);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('test', $task['title']);
    }

    public function testDuplicateAnotherProjectWithPredefinedCategory()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $categoryModel = new CategoryModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        $this->assertNotFalse($categoryModel->create(['name' => 'Category #1', 'project_id' => 1]));
        $this->assertNotFalse($categoryModel->create(['name' => 'Category #1', 'project_id' => 2]));
        $this->assertNotFalse($categoryModel->create(['name' => 'Category #2', 'project_id' => 2]));
        $this->assertTrue($categoryModel->exists(1));
        $this->assertTrue($categoryModel->exists(2));
        $this->assertTrue($categoryModel->exists(3));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 2, 'category_id' => 1]));

        // We duplicate our task to the 2nd project with no category
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2, null, null, 0));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(0, $task['category_id']);

        // We duplicate our task to the 2nd project with a different category
        $this->assertEquals(3, $taskProjectDuplicationModel->duplicateToProject(1, 2, null, null, 3));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(3);
        $this->assertNotEmpty($task);
        $this->assertEquals(3, $task['category_id']);
    }

    public function testDuplicateAnotherProjectWithSwimlane()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        $this->assertNotFalse($swimlaneModel->create(['project_id' => 1, 'name' => 'Swimlane #1']));
        $this->assertNotFalse($swimlaneModel->create(['project_id' => 2, 'name' => 'Swimlane #1']));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 2, 'swimlane_id' => 1]));

        // We duplicate our task to the 2nd project
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(0, $task['owner_id']);
        $this->assertEquals(0, $task['category_id']);
        $this->assertEquals(2, $task['swimlane_id']);
        $this->assertEquals(6, $task['column_id']);
        $this->assertEquals(1, $task['position']);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('test', $task['title']);
    }

    public function testDuplicateAnotherProjectWithoutSwimlane()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        $this->assertNotFalse($swimlaneModel->create(['project_id' => 1, 'name' => 'Swimlane #1']));
        $this->assertNotFalse($swimlaneModel->create(['project_id' => 2, 'name' => 'Swimlane #2']));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 2, 'swimlane_id' => 1]));

        // We duplicate our task to the 2nd project
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(0, $task['owner_id']);
        $this->assertEquals(0, $task['category_id']);
        $this->assertEquals(0, $task['swimlane_id']);
        $this->assertEquals(6, $task['column_id']);
        $this->assertEquals(1, $task['position']);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('test', $task['title']);
    }

    public function testDuplicateAnotherProjectWithPredefinedSwimlane()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        $this->assertNotFalse($swimlaneModel->create(['project_id' => 1, 'name' => 'Swimlane #1']));
        $this->assertNotFalse($swimlaneModel->create(['project_id' => 2, 'name' => 'Swimlane #1']));
        $this->assertNotFalse($swimlaneModel->create(['project_id' => 2, 'name' => 'Swimlane #2']));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 2, 'swimlane_id' => 1]));

        // We duplicate our task to the 2nd project
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2, 3));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(3, $task['swimlane_id']);
    }

    public function testDuplicateAnotherProjectWithPredefinedColumn()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 2]));

        // We duplicate our task to the 2nd project with a different column
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2, null, 7));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(7, $task['column_id']);
    }

    public function testDuplicateAnotherProjectWithUser()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 2, 'owner_id' => 2]));

        // We duplicate our task to the 2nd project
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(0, $task['owner_id']);
        $this->assertEquals(6, $task['column_id']);
        $this->assertEquals(1, $task['position']);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals('test', $task['title']);

        // We create a new user for our project
        $user = new UserModel($this->container);
        $this->assertNotFalse($user->create(['username' => 'unittest#1', 'email' => 'unittest@unittest', 'password' => 'unittest']));
        $this->assertTrue($projectUserRoleModel->addUser(1, 2, Role::PROJECT_MEMBER));
        $this->assertTrue($projectUserRoleModel->addUser(2, 2, Role::PROJECT_MEMBER));

        // We duplicate our task to the 2nd project
        $this->assertEquals(3, $taskProjectDuplicationModel->duplicateToProject(1, 2));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(3);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['position']);
        $this->assertEquals(6, $task['column_id']);
        $this->assertEquals(2, $task['owner_id']);
        $this->assertEquals(2, $task['project_id']);

        // We duplicate a task with a not allowed user
        $this->assertEquals(4, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 3]));
        $this->assertEquals(5, $taskProjectDuplicationModel->duplicateToProject(4, 2));

        $task = $taskFinderModel->getById(5);
        $this->assertNotEmpty($task);
        $this->assertEquals(1, $task['position']);
        $this->assertEquals(0, $task['owner_id']);
        $this->assertEquals(2, $task['project_id']);
        $this->assertEquals(5, $task['column_id']);
    }

    public function testDuplicateAnotherProjectWithPredefinedUser()
    {
        $taskProjectDuplicationModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'column_id' => 2, 'owner_id' => 2]));
        $this->assertTrue($projectUserRoleModel->addUser(2, 1, Role::PROJECT_MEMBER));

        // We duplicate our task to the 2nd project
        $this->assertEquals(2, $taskProjectDuplicationModel->duplicateToProject(1, 2, null, null, null, 1));

        // Check the values of the duplicated task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(1, $task['owner_id']);
    }

    public function testDuplicateAnotherProjectWithDifferentTags()
    {
        $taskProjectMoveModel = new TaskProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $tagModel = new TagModel($this->container);
        $taskTagModel = new TaskTagModel($this->container);

        // We create 2 projects
        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));

        // We create our tags for each projects
        $this->assertEquals(1, $tagModel->create(1, 'T1'));
        $this->assertEquals(2, $tagModel->create(1, 'T2'));
        $this->assertEquals(3, $tagModel->create(2, 'T2'));
        $this->assertEquals(4, $tagModel->create(2, 'T3'));
        $this->assertEquals(5, $tagModel->create(0, 'T4'));
        $this->assertEquals(6, $tagModel->create(0, 'T5'));

        // We create a task
        $this->assertEquals(1, $taskModel->create(['title' => 'test', 'project_id' => 1, 'tags' => ['T1', 'T2', 'T5']]));

        // We move our task to the 2nd project
        $this->assertEquals(2, $taskProjectMoveModel->duplicateToProject(1, 2));

        // Check the values of the moved task
        $task = $taskFinderModel->getById(2);
        $this->assertNotEmpty($task);
        $this->assertEquals(2, $task['id']);
        $this->assertEquals(2, $task['project_id']);

        // Check tags
        $tags = $taskTagModel->getList(2);
        $this->assertCount(2, $tags);
        $this->assertArrayHasKey(3, $tags);
        $this->assertArrayHasKey(6, $tags);
    }
}
