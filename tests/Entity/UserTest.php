<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Entity\User
 * @package \App\Tests
 */
class UserTest extends TestCase
{
    public function testGetId(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
    }

    public function testGetUsername(): void
    {
        $user = new User();
        $this->assertNull($user->getUsername());
    }

    public function testSetUsername(): void
    {
        $user = new User();
        $username = 'test_username';
        $user->setUsername($username);
        $this->assertSame($username, $user->getUsername());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $username = 'test_username';
        $user->setUsername($username);
        $this->assertSame($username, $user->getUserIdentifier());
    }

    public function testGetRoles(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetRoles(): void
    {
        $user = new User();
        $roles = ['ROLE_ADMIN', 'ROLE_USER'];
        $user->setRoles($roles);
        $this->assertSame($roles, $user->getRoles());
    }

    public function testGetPassword(): void
    {
        $user = new User();
        $this->assertNull($user->getPassword());
    }

    public function testSetPassword(): void
    {
        $user = new User();
        $password = 'test_password';
        $user->setPassword($password);
        $this->assertSame($password, $user->getPassword());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->eraseCredentials();
        // There's no way to test the actual result of this method, so we just
        // check that it doesn't throw any exceptions
        $this->assertTrue(true);
    }

    public function testGetEmail(): void
    {
        $user = new User();
        $this->assertNull($user->getEmail());
    }

    public function testSetEmail(): void
    {
        $user = new User();
        $email = 'test@example.com';
        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
    }

    public function testGetTasks(): void
    {
        $user = new User();
        $this->assertInstanceOf(Collection::class, $user->getTasks());
    }

    public function testAddTask(): void
    {
        $user = new User();
        $task = new Task();
        $user->addTask($task);
        $this->assertContains($task, $user->getTasks());
    }

    public function testRemoveTask(): void
    {
        $user = new User();
        $task = new Task();
        $user->addTask($task);
        $user->removeTask($task);
        $this->assertNotContains($task, $user->getTasks());
    }

    public function testToString(): void
    {
        $user = new User();
        $user->setUsername('test_username');
        $result = (string)$user;

        $this->assertEquals('test_username', $result);
    }

}