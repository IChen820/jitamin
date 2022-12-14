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

use Jitamin\Model\PasswordResetModel;
use Jitamin\Model\UserModel;

class PasswordResetTest extends Base
{
    public function testCreate()
    {
        $userModel = new UserModel($this->container);
        $passwordResetModel = new PasswordResetModel($this->container);

        $this->assertEquals(2, $userModel->create(['username' => 'user1', 'email' => 'user1@localhost']));
        $this->assertEquals(3, $userModel->create(['username' => 'user2', 'email' => 'user2@localhost']));

        $this->assertFalse($passwordResetModel->create(4));
        $this->assertNotFalse($passwordResetModel->create(2));
        $this->assertNotFalse($passwordResetModel->create(3));
    }

    public function testGetUserIdByToken()
    {
        $userModel = new UserModel($this->container);
        $passwordResetModel = new PasswordResetModel($this->container);

        $this->assertEquals(2, $userModel->create(['username' => 'user2', 'email' => 'user2@localhost']));

        $token = $passwordResetModel->create(2);
        $this->assertEquals(2, $passwordResetModel->getUserIdByToken($token));
    }

    public function testGetUserIdByTokenWhenExpired()
    {
        $userModel = new UserModel($this->container);
        $passwordResetModel = new PasswordResetModel($this->container);

        $this->assertEquals(2, $userModel->create(['username' => 'user2', 'email' => 'user2@localhost']));

        $token = $passwordResetModel->create(2, strtotime('-1 year'));
        $this->assertFalse($passwordResetModel->getUserIdByToken($token));
    }

    public function testDisableTokens()
    {
        $userModel = new UserModel($this->container);
        $passwordResetModel = new PasswordResetModel($this->container);

        $this->assertEquals(2, $userModel->create(['username' => 'user2', 'email' => 'user2@localhost']));

        $token1 = $passwordResetModel->create(2);
        $token2 = $passwordResetModel->create(2);

        $this->assertEquals(2, $passwordResetModel->getUserIdByToken($token1));
        $this->assertEquals(2, $passwordResetModel->getUserIdByToken($token2));

        $this->assertTrue($passwordResetModel->disable(2));

        $this->assertFalse($passwordResetModel->getUserIdByToken($token1));
        $this->assertFalse($passwordResetModel->getUserIdByToken($token2));
    }

    public function testGetAll()
    {
        $userModel = new UserModel($this->container);
        $passwordResetModel = new PasswordResetModel($this->container);

        $this->assertEquals(2, $userModel->create(['username' => 'user2', 'email' => 'user2@localhost']));
        $this->assertNotFalse($passwordResetModel->create(2));
        $this->assertNotFalse($passwordResetModel->create(2));

        $tokens = $passwordResetModel->getAll(1);
        $this->assertCount(0, $tokens);

        $tokens = $passwordResetModel->getAll(2);
        $this->assertCount(2, $tokens);
        $this->assertNotEmpty($tokens[0]['token']);
        $this->assertNotEmpty($tokens[0]['date_creation']);
        $this->assertNotEmpty($tokens[0]['date_expiration']);
        $this->assertEquals(2, $tokens[0]['user_id']);
        $this->assertArrayHasKey('user_agent', $tokens[0]);
        $this->assertArrayHasKey('ip', $tokens[0]);
    }
}
