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
use Jitamin\Model\ActionModel;
use Jitamin\Model\CategoryModel;
use Jitamin\Model\GroupMemberModel;
use Jitamin\Model\GroupModel;
use Jitamin\Model\ProjectDuplicationModel;
use Jitamin\Model\ProjectGroupRoleModel;
use Jitamin\Model\ProjectModel;
use Jitamin\Model\ProjectUserRoleModel;
use Jitamin\Model\SwimlaneModel;
use Jitamin\Model\TagModel;
use Jitamin\Model\TaskFinderModel;
use Jitamin\Model\TaskModel;
use Jitamin\Model\TaskTagModel;
use Jitamin\Model\UserModel;

class ProjectDuplicationModelTest extends Base
{
    public function testGetSelections()
    {
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);
        $this->assertCount(7, $projectDuplicationModel->getOptionalSelection());
        $this->assertCount(8, $projectDuplicationModel->getPossibleSelection());
    }

    public function testGetClonedProjectName()
    {
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals('test (Clone)', $projectDuplicationModel->getClonedProjectName('test'));

        $this->assertEquals(50, strlen($projectDuplicationModel->getClonedProjectName(str_repeat('a', 50))));
        $this->assertEquals(str_repeat('a', 42).' (Clone)', $projectDuplicationModel->getClonedProjectName(str_repeat('a', 50)));

        $this->assertEquals(50, strlen($projectDuplicationModel->getClonedProjectName(str_repeat('a', 60))));
        $this->assertEquals(str_repeat('a', 42).' (Clone)', $projectDuplicationModel->getClonedProjectName(str_repeat('a', 60)));
    }

    public function testClonePublicProject()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Public']));
        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('Public (Clone)', $project['name']);
        $this->assertEquals(1, $project['is_active']);
        $this->assertEquals(0, $project['is_private']);
        $this->assertEquals(0, $project['is_public']);
        $this->assertEquals(0, $project['owner_id']);
        $this->assertEmpty($project['token']);
    }

    public function testClonePrivateProject()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Private', 'is_private' => 1], 1, true));
        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('Private (Clone)', $project['name']);
        $this->assertEquals(1, $project['is_active']);
        $this->assertEquals(1, $project['is_private']);
        $this->assertEquals(0, $project['is_public']);
        $this->assertEquals(0, $project['owner_id']);
        $this->assertEmpty($project['token']);

        $this->assertEquals(Role::PROJECT_MANAGER, $projectUserRoleModel->getUserRole(2, 1));
    }

    public function testCloneSharedProject()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Shared']));
        $this->assertTrue($projectModel->update(['id' => 1, 'is_public' => 1, 'token' => 'test']));

        $project = $projectModel->getById(1);
        $this->assertEquals('test', $project['token']);
        $this->assertEquals(1, $project['is_public']);

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('Shared (Clone)', $project['name']);
        $this->assertEquals('', $project['token']);
        $this->assertEquals(0, $project['is_public']);
    }

    public function testCloneInactiveProject()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Inactive']));
        $this->assertTrue($projectModel->update(['id' => 1, 'is_active' => 0]));

        $project = $projectModel->getById(1);
        $this->assertEquals(0, $project['is_active']);

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('Inactive (Clone)', $project['name']);
        $this->assertEquals(1, $project['is_active']);
    }

    public function testCloneProjectWithOwner()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Owner']));

        $project = $projectModel->getById(1);
        $this->assertEquals(0, $project['owner_id']);

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['projectPermissionModel'], 1));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('Owner (Clone)', $project['name']);
        $this->assertEquals(1, $project['owner_id']);

        $this->assertEquals(Role::PROJECT_MANAGER, $projectUserRoleModel->getUserRole(2, 1));
    }

    public function testCloneProjectWithDifferentPriorities()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create([
            'name'             => 'My project',
            'priority_default' => 2,
            'priority_start'   => -2,
            'priority_end'     => 8,
        ]));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('My project (Clone)', $project['name']);
        $this->assertEquals(2, $project['priority_default']);
        $this->assertEquals(-2, $project['priority_start']);
        $this->assertEquals(8, $project['priority_end']);
    }

    public function testCloneProjectWithDifferentName()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Owner']));

        $project = $projectModel->getById(1);
        $this->assertEquals(0, $project['owner_id']);

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['projectPermissionModel'], 1, 'Foobar'));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('Foobar', $project['name']);
        $this->assertEquals(1, $project['owner_id']);
    }

    public function testCloneProjectAndForceItToBePrivate()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Owner']));

        $project = $projectModel->getById(1);
        $this->assertEquals(0, $project['owner_id']);
        $this->assertEquals(0, $project['is_private']);

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['projectPermissionModel'], 1, 'Foobar', true));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('Foobar', $project['name']);
        $this->assertEquals(1, $project['owner_id']);
        $this->assertEquals(1, $project['is_private']);
    }

    public function testCloneProjectWithCategories()
    {
        $projectModel = new ProjectModel($this->container);
        $categoryModel = new CategoryModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'P1']));

        $this->assertEquals(1, $categoryModel->create(['name' => 'C1', 'project_id' => 1, 'position' => 1]));
        $this->assertEquals(2, $categoryModel->create(['name' => 'C2', 'project_id' => 1, 'position' => 2]));
        $this->assertEquals(3, $categoryModel->create(['name' => 'C3', 'project_id' => 1, 'position' => 3]));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $project = $projectModel->getById(2);
        $this->assertNotEmpty($project);
        $this->assertEquals('P1 (Clone)', $project['name']);

        $categories = $categoryModel->getAll(2);
        $this->assertCount(3, $categories);
        $this->assertEquals('C1', $categories[0]['name']);
        $this->assertEquals('C2', $categories[1]['name']);
        $this->assertEquals('C3', $categories[2]['name']);
    }

    public function testCloneProjectWithUsers()
    {
        $projectModel = new ProjectModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);
        $userModel = new UserModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(2, $userModel->create(['username' => 'user1', 'email' => 'user1@here']));
        $this->assertEquals(3, $userModel->create(['username' => 'user2', 'email' => 'user2@here']));
        $this->assertEquals(4, $userModel->create(['username' => 'user3', 'email' => 'user3@here']));

        $this->assertEquals(1, $projectModel->create(['name' => 'P1']));

        $this->assertTrue($projectUserRoleModel->addUser(1, 2, Role::PROJECT_MANAGER));
        $this->assertTrue($projectUserRoleModel->addUser(1, 3, Role::PROJECT_MEMBER));
        $this->assertTrue($projectUserRoleModel->addUser(1, 4, Role::PROJECT_VIEWER));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $this->assertCount(3, $projectUserRoleModel->getUsers(2));
        $this->assertEquals(Role::PROJECT_MANAGER, $projectUserRoleModel->getUserRole(2, 2));
        $this->assertEquals(Role::PROJECT_MEMBER, $projectUserRoleModel->getUserRole(2, 3));
        $this->assertEquals(Role::PROJECT_VIEWER, $projectUserRoleModel->getUserRole(2, 4));
    }

    public function testCloneProjectWithUsersAndOverrideOwner()
    {
        $projectModel = new ProjectModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);
        $userModel = new UserModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(2, $userModel->create(['username' => 'user1', 'email' => 'user1@here']));
        $this->assertEquals(1, $projectModel->create(['name' => 'P1'], 2));

        $project = $projectModel->getById(1);
        $this->assertEquals(2, $project['owner_id']);

        $this->assertTrue($projectUserRoleModel->addUser(1, 2, Role::PROJECT_MANAGER));
        $this->assertTrue($projectUserRoleModel->addUser(1, 1, Role::PROJECT_MEMBER));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['projectPermissionModel'], 1));

        $this->assertCount(2, $projectUserRoleModel->getUsers(2));
        $this->assertEquals(Role::PROJECT_MANAGER, $projectUserRoleModel->getUserRole(2, 2));
        $this->assertEquals(Role::PROJECT_MANAGER, $projectUserRoleModel->getUserRole(2, 1));

        $project = $projectModel->getById(2);
        $this->assertEquals(1, $project['owner_id']);
    }

    public function testCloneTeamProjectToPrivatProject()
    {
        $projectModel = new ProjectModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);
        $userModel = new UserModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(2, $userModel->create(['username' => 'user1', 'email' => 'user1@here']));
        $this->assertEquals(3, $userModel->create(['username' => 'user2', 'email' => 'user2@here']));
        $this->assertEquals(1, $projectModel->create(['name' => 'P1'], 2));

        $project = $projectModel->getById(1);
        $this->assertEquals(2, $project['owner_id']);
        $this->assertEquals(0, $project['is_private']);

        $this->assertTrue($projectUserRoleModel->addUser(1, 2, Role::PROJECT_MANAGER));
        $this->assertTrue($projectUserRoleModel->addUser(1, 1, Role::PROJECT_MEMBER));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['projectPermissionModel'], 3, 'My private project', true));

        $this->assertCount(1, $projectUserRoleModel->getUsers(2));
        $this->assertEquals(Role::PROJECT_MANAGER, $projectUserRoleModel->getUserRole(2, 3));

        $project = $projectModel->getById(2);
        $this->assertEquals(3, $project['owner_id']);
        $this->assertEquals(1, $project['is_private']);
    }

    public function testCloneProjectWithGroups()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);
        $userModel = new UserModel($this->container);
        $groupModel = new GroupModel($this->container);
        $groupMemberModel = new GroupMemberModel($this->container);
        $projectGroupRoleModel = new ProjectGroupRoleModel($this->container);
        $projectUserRoleModel = new ProjectUserRoleModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'P1']));

        $this->assertEquals(1, $groupModel->create('G1'));
        $this->assertEquals(2, $groupModel->create('G2'));
        $this->assertEquals(3, $groupModel->create('G3'));

        $this->assertEquals(2, $userModel->create(['username' => 'user1', 'email' => 'user1@here']));
        $this->assertEquals(3, $userModel->create(['username' => 'user2', 'email' => 'user2@here']));
        $this->assertEquals(4, $userModel->create(['username' => 'user3', 'email' => 'user3@here']));

        $this->assertTrue($groupMemberModel->addUser(1, 2));
        $this->assertTrue($groupMemberModel->addUser(2, 3));
        $this->assertTrue($groupMemberModel->addUser(3, 4));

        $this->assertTrue($projectGroupRoleModel->addGroup(1, 1, Role::PROJECT_MANAGER));
        $this->assertTrue($projectGroupRoleModel->addGroup(1, 2, Role::PROJECT_MEMBER));
        $this->assertTrue($projectGroupRoleModel->addGroup(1, 3, Role::PROJECT_VIEWER));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $this->assertCount(3, $projectGroupRoleModel->getGroups(2));
        $this->assertEquals(Role::PROJECT_MANAGER, $projectUserRoleModel->getUserRole(2, 2));
        $this->assertEquals(Role::PROJECT_MEMBER, $projectUserRoleModel->getUserRole(2, 3));
        $this->assertEquals(Role::PROJECT_VIEWER, $projectUserRoleModel->getUserRole(2, 4));
    }

    public function testCloneProjectWithActionTaskAssignCurrentUser()
    {
        $projectModel = new ProjectModel($this->container);
        $actionModel = new ActionModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'P1']));

        $this->assertEquals(1, $actionModel->create([
            'project_id'  => 1,
            'event_name'  => TaskModel::EVENT_MOVE_COLUMN,
            'action_name' => 'TaskAssignCurrentUser',
            'params'      => ['column_id' => 2],
        ]));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $actions = $actionModel->getAllByProject(2);

        $this->assertNotEmpty($actions);
        $this->assertEquals('TaskAssignCurrentUser', $actions[0]['action_name']);
        $this->assertNotEmpty($actions[0]['params']);
        $this->assertEquals(6, $actions[0]['params']['column_id']);
    }

    public function testCloneProjectWithActionTaskAssignColorCategory()
    {
        $projectModel = new ProjectModel($this->container);
        $actionModel = new ActionModel($this->container);
        $categoryModel = new CategoryModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'P1']));

        $this->assertEquals(1, $categoryModel->create(['name' => 'C1', 'project_id' => 1]));
        $this->assertEquals(2, $categoryModel->create(['name' => 'C2', 'project_id' => 1]));
        $this->assertEquals(3, $categoryModel->create(['name' => 'C3', 'project_id' => 1]));

        $this->assertEquals(1, $actionModel->create([
            'project_id'  => 1,
            'event_name'  => TaskModel::EVENT_CREATE_UPDATE,
            'action_name' => 'TaskAssignColorCategory',
            'params'      => ['color_id' => 'blue', 'category_id' => 2],
        ]));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1));

        $actions = $actionModel->getAllByProject(2);

        $this->assertNotEmpty($actions);
        $this->assertEquals('TaskAssignColorCategory', $actions[0]['action_name']);
        $this->assertNotEmpty($actions[0]['params']);
        $this->assertEquals('blue', $actions[0]['params']['color_id']);
        $this->assertEquals(5, $actions[0]['params']['category_id']);
    }

    public function testCloneProjectWithSwimlanes()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'P1', 'default_swimlane' => 'New Default']));

        // create initial swimlanes
        $this->assertEquals(1, $swimlaneModel->create(['project_id' => 1, 'name' => 'S1']));
        $this->assertEquals(2, $swimlaneModel->create(['project_id' => 1, 'name' => 'S2']));
        $this->assertEquals(3, $swimlaneModel->create(['project_id' => 1, 'name' => 'S3']));

        // create initial tasks
        $this->assertEquals(1, $taskModel->create(['title' => 'T0', 'project_id' => 1, 'swimlane_id' => 0]));
        $this->assertEquals(2, $taskModel->create(['title' => 'T1', 'project_id' => 1, 'swimlane_id' => 1]));
        $this->assertEquals(3, $taskModel->create(['title' => 'T2', 'project_id' => 1, 'swimlane_id' => 2]));
        $this->assertEquals(4, $taskModel->create(['title' => 'T3', 'project_id' => 1, 'swimlane_id' => 3]));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['categoryModel', 'swimlaneModel']));

        $swimlanes = $swimlaneModel->getAll(2);
        $this->assertCount(3, $swimlanes);
        $this->assertEquals(4, $swimlanes[0]['id']);
        $this->assertEquals('S1', $swimlanes[0]['name']);
        $this->assertEquals(5, $swimlanes[1]['id']);
        $this->assertEquals('S2', $swimlanes[1]['name']);
        $this->assertEquals(6, $swimlanes[2]['id']);
        $this->assertEquals('S3', $swimlanes[2]['name']);

        $swimlane = $swimlaneModel->getDefault(2);
        $this->assertEquals('New Default', $swimlane['default_swimlane']);

        // Check if tasks are NOT been duplicated
        $this->assertCount(0, $taskFinderModel->getAll(2));
    }

    public function testCloneProjectWithTasks()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'P1']));

        // create initial tasks
        $this->assertEquals(1, $taskModel->create(['title' => 'T1', 'project_id' => 1, 'column_id' => 1]));
        $this->assertEquals(2, $taskModel->create(['title' => 'T2', 'project_id' => 1, 'column_id' => 2]));
        $this->assertEquals(3, $taskModel->create(['title' => 'T3', 'project_id' => 1, 'column_id' => 3]));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['categoryModel', 'actionModel', 'projectTaskDuplicationModel']));

        // Check if Tasks have been duplicated
        $tasks = $taskFinderModel->getAll(2);
        $this->assertCount(3, $tasks);
        $this->assertEquals('T1', $tasks[0]['title']);
        $this->assertEquals('T2', $tasks[1]['title']);
        $this->assertEquals('T3', $tasks[2]['title']);
    }

    public function testCloneProjectWithSwimlanesAndTasks()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);
        $swimlaneModel = new SwimlaneModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'P1', 'default_swimlane' => 'New Default']));

        // create initial swimlanes
        $this->assertEquals(1, $swimlaneModel->create(['project_id' => 1, 'name' => 'S1']));
        $this->assertEquals(2, $swimlaneModel->create(['project_id' => 1, 'name' => 'S2']));
        $this->assertEquals(3, $swimlaneModel->create(['project_id' => 1, 'name' => 'S3']));

        // create initial tasks
        $this->assertEquals(1, $taskModel->create(['title' => 'T1', 'project_id' => 1, 'column_id' => 1, 'owner_id' => 1]));
        $this->assertEquals(2, $taskModel->create(['title' => 'T2', 'project_id' => 1, 'column_id' => 2, 'owner_id' => 1]));
        $this->assertEquals(3, $taskModel->create(['title' => 'T3', 'project_id' => 1, 'column_id' => 3, 'owner_id' => 1]));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['projectPermissionModel', 'swimlaneModel', 'projectTaskDuplicationModel']));

        // Check if Swimlanes have been duplicated
        $swimlanes = $swimlaneModel->getAll(2);
        $this->assertCount(3, $swimlanes);
        $this->assertEquals(4, $swimlanes[0]['id']);
        $this->assertEquals('S1', $swimlanes[0]['name']);
        $this->assertEquals(5, $swimlanes[1]['id']);
        $this->assertEquals('S2', $swimlanes[1]['name']);
        $this->assertEquals(6, $swimlanes[2]['id']);
        $this->assertEquals('S3', $swimlanes[2]['name']);

        $swimlane = $swimlaneModel->getDefault(2);
        $this->assertEquals('New Default', $swimlane['default_swimlane']);

        // Check if Tasks have been duplicated
        $tasks = $taskFinderModel->getAll(2);

        $this->assertCount(3, $tasks);
        $this->assertEquals('T1', $tasks[0]['title']);
        $this->assertEquals('T2', $tasks[1]['title']);
        $this->assertEquals('T3', $tasks[2]['title']);
    }

    public function testCloneProjectWithTags()
    {
        $projectModel = new ProjectModel($this->container);
        $projectDuplicationModel = new ProjectDuplicationModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $tagModel = new TagModel($this->container);
        $taskTagModel = new TaskTagModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'P1']));
        $this->assertEquals(1, $taskModel->create(['title' => 'T1', 'project_id' => 1, 'column_id' => 1, 'tags' => ['A']]));
        $this->assertEquals(2, $taskModel->create(['title' => 'T2', 'project_id' => 1, 'column_id' => 2, 'tags' => ['A', 'B']]));
        $this->assertEquals(3, $taskModel->create(['title' => 'T3', 'project_id' => 1, 'column_id' => 3, 'tags' => ['C']]));

        $this->assertEquals(2, $projectDuplicationModel->duplicate(1, ['categoryModel', 'actionModel', 'tagDuplicationModel', 'projectTaskDuplicationModel']));

        $tasks = $taskFinderModel->getAll(2);
        $this->assertCount(3, $tasks);
        $this->assertEquals('T1', $tasks[0]['title']);
        $this->assertEquals('T2', $tasks[1]['title']);
        $this->assertEquals('T3', $tasks[2]['title']);

        $tags = $tagModel->getAllByProject(2);
        $this->assertCount(3, $tags);
        $this->assertEquals(4, $tags[0]['id']);
        $this->assertEquals('A', $tags[0]['name']);
        $this->assertEquals(5, $tags[1]['id']);
        $this->assertEquals('B', $tags[1]['name']);
        $this->assertEquals(6, $tags[2]['id']);
        $this->assertEquals('C', $tags[2]['name']);

        $tags = $taskTagModel->getList(4);
        $this->assertEquals('A', $tags[4]);

        $tags = $taskTagModel->getList(5);
        $this->assertEquals('A', $tags[4]);
        $this->assertEquals('B', $tags[5]);

        $tags = $taskTagModel->getList(6);
        $this->assertEquals('C', $tags[6]);
    }
}
