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

use Jitamin\Model\GroupModel;

class GroupModelTest extends Base
{
    public function testCreation()
    {
        $groupModel = new GroupModel($this->container);
        $this->assertEquals(1, $groupModel->create('Test'));
        $this->assertFalse($groupModel->create('Test'));
    }

    public function testGetById()
    {
        $groupModel = new GroupModel($this->container);
        $this->assertEquals(1, $groupModel->create('Test'));

        $group = $groupModel->getById(1);
        $this->assertEquals('Test', $group['name']);
        $this->assertEquals('', $group['external_id']);

        $this->assertEmpty($groupModel->getById(2));
    }

    public function testGetAll()
    {
        $groupModel = new GroupModel($this->container);
        $this->assertEquals(1, $groupModel->create('B'));
        $this->assertEquals(2, $groupModel->create('A', 'uuid'));

        $groups = $groupModel->getAll();
        $this->assertCount(2, $groups);
        $this->assertEquals('A', $groups[0]['name']);
        $this->assertEquals('uuid', $groups[0]['external_id']);
        $this->assertEquals('B', $groups[1]['name']);
        $this->assertEquals('', $groups[1]['external_id']);
    }

    public function testUpdate()
    {
        $groupModel = new GroupModel($this->container);
        $this->assertEquals(1, $groupModel->create('Test'));
        $this->assertTrue($groupModel->update(['id' => 1, 'name' => 'My group', 'external_id' => 'test']));

        $group = $groupModel->getById(1);
        $this->assertEquals('My group', $group['name']);
        $this->assertEquals('test', $group['external_id']);
    }

    public function testRemove()
    {
        $groupModel = new GroupModel($this->container);
        $this->assertEquals(1, $groupModel->create('Test'));
        $this->assertTrue($groupModel->remove(1));
        $this->assertEmpty($groupModel->getById(1));
    }

    public function testGetOrCreateExternalGroupId()
    {
        $groupModel = new GroupModel($this->container);
        $this->assertEquals(1, $groupModel->create('Group 1', 'ExternalId1'));
        $this->assertEquals(1, $groupModel->getOrCreateExternalGroupId('Group 1', 'ExternalId1'));
        $this->assertEquals(1, $groupModel->getOrCreateExternalGroupId('Group 2', 'ExternalId1'));
        $this->assertEquals(2, $groupModel->getOrCreateExternalGroupId('Group 2', 'ExternalId2'));
    }
}
