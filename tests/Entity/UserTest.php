<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetId()
    {
        $user = new User();
        $this->assertNull($user->getId());
    }

    public function testGetUsername()
    {
        $user = new User();
        $this->assertNull($user->getUsername());
    }

    public function testSetUsername()
    {
        $user = new User();
        $username = 'test_username';
        $user->setUsername($username);
        $this->assertSame($username, $user->getUsername());
    }

    public function testGetUserIdentifier()
    {
        $user = new User();
        $username = 'test_username';
        $user->setUsername($username);
        $this->assertSame($username, $user->getUserIdentifier());
    }

    public function testGetRoles()
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetRoles()
    {
        $user = new User();
        $roles = ['ROLE_ADMIN', 'ROLE_USER'];
        $user->setRoles($roles);
        $this->assertSame($roles, $user->getRoles());
    }

    public function testGetPassword()
    {
        $user = new User();
        $this->assertNull($user->getPassword());
    }

    public function testSetPassword()
    {
        $user = new User();
        $password = 'test_password';
        $user->setPassword($password);
        $this->assertSame($password, $user->getPassword());
    }

    public function testEraseCredentials()
    {
        $user = new User();
        $user->eraseCredentials();
        // There's no way to test the actual result of this method, so we just
        // check that it doesn't throw any exceptions
        $this->assertTrue(true);
    }

    public function testGetEmail()
    {
        $user = new User();
        $this->assertNull($user->getEmail());
    }

    public function testSetEmail()
    {
        $user = new User();
        $email = 'test@example.com';
        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
    }

    public function testGetTasks()
    {
        $user = new User();
        $this->assertInstanceOf(Collection::class, $user->getTasks());
    }

    public function testAddTask()
    {
        $user = new User();
        $task = new Task();
        $user->addTask($task);
        $this->assertContains($task, $user->getTasks());
    }

    public function testRemoveTask()
    {
        $user = new User();
        $task = new Task();
        $user->addTask($task);
        $user->removeTask($task);
        $this->assertNotContains($task, $user->getTasks());
    }

//    public function testGetSalt()
//    {
//        $user = new User();
//        $this->assertNull($user->getSalt());
//    }

    public function testToString()
    {
        $user = new User();
        $username = 'test_username';
        $user->setUsername($username);
        $this->assertSame($username, $user->__toString());
    }

}