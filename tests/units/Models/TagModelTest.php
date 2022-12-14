<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Jitamin\Model\ProjectModel;
use Jitamin\Model\TagModel;

require_once __DIR__.'/../Base.php';

class TagModelTest extends Base
{
    public function testCreation()
    {
        $tagModel = new TagModel($this->container);
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $tagModel->create(0, 'Tag 1'));
        $this->assertEquals(2, $tagModel->create(1, 'Tag 1'));
        $this->assertEquals(3, $tagModel->create(1, 'Tag 2'));
        $this->assertFalse($tagModel->create(0, 'Tag 1'));
        $this->assertFalse($tagModel->create(1, 'Tag 2'));
    }

    public function testGetById()
    {
        $tagModel = new TagModel($this->container);
        $this->assertEquals(1, $tagModel->create(0, 'Tag 1'));

        $tag = $tagModel->getById(1);
        $this->assertEquals(0, $tag['project_id']);
        $this->assertEquals('Tag 1', $tag['name']);

        $tag = $tagModel->getById(3);
        $this->assertEmpty($tag);
    }

    public function testGetAll()
    {
        $tagModel = new TagModel($this->container);
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $tagModel->create(0, 'Tag 1'));
        $this->assertEquals(2, $tagModel->create(1, 'Tag 2'));

        $tags = $tagModel->getAll();
        $this->assertCount(2, $tags);
        $this->assertEquals(0, $tags[0]['project_id']);
        $this->assertEquals('Tag 1', $tags[0]['name']);

        $this->assertEquals(1, $tags[1]['project_id']);
        $this->assertEquals('Tag 2', $tags[1]['name']);
    }

    public function testGetAllByProjectId()
    {
        $tagModel = new TagModel($this->container);
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $tagModel->create(0, 'Tag 1'));
        $this->assertEquals(2, $tagModel->create(1, 'B'));
        $this->assertEquals(3, $tagModel->create(1, 'A'));

        $tags = $tagModel->getAllByProject(1);
        $this->assertCount(2, $tags);
        $this->assertEquals(1, $tags[0]['project_id']);
        $this->assertEquals('A', $tags[0]['name']);

        $this->assertEquals(1, $tags[1]['project_id']);
        $this->assertEquals('B', $tags[1]['name']);
    }

    public function testGetIdByName()
    {
        $tagModel = new TagModel($this->container);
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $tagModel->create(0, 'Tag 1'));
        $this->assertEquals(2, $tagModel->create(1, 'Tag 1'));
        $this->assertEquals(3, $tagModel->create(1, 'Tag 3'));

        $this->assertEquals(1, $tagModel->getIdByName(1, 'tag 1'));
        $this->assertEquals(1, $tagModel->getIdByName(0, 'tag 1'));
        $this->assertEquals(3, $tagModel->getIdByName(1, 'TaG 3'));
    }

    public function testFindOrCreateTag()
    {
        $tagModel = new TagModel($this->container);
        $projectModel = new ProjectModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $tagModel->create(0, 'Tag 1'));

        $this->assertEquals(2, $tagModel->findOrCreateTag(1, 'Tag 2'));
        $this->assertEquals(2, $tagModel->findOrCreateTag(1, 'Tag 2'));
        $this->assertEquals(1, $tagModel->findOrCreateTag(1, 'Tag 1'));
    }

    public function testRemove()
    {
        $tagModel = new TagModel($this->container);
        $this->assertEquals(1, $tagModel->create(0, 'Tag 1'));

        $this->assertTrue($tagModel->remove(1));
        $this->assertFalse($tagModel->remove(1));
    }

    public function testUpdate()
    {
        $tagModel = new TagModel($this->container);
        $this->assertEquals(1, $tagModel->create(0, 'Tag 1'));
        $this->assertTrue($tagModel->update(1, 'Tag Updated'));

        $tag = $tagModel->getById(1);
        $this->assertEquals(0, $tag['project_id']);
        $this->assertEquals('Tag Updated', $tag['name']);
    }
}
