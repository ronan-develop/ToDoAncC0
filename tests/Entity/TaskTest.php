<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Entity\Task
 * @package App\Tests
 */
class TaskTest extends KernelTestCase
{
    public function testGetCreatedAt(): void
    {
        $task = new Task();
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
    }

    public function testSetCreatedAt(): void
    {
        $task = new Task();
        $createdAt = new \DateTimeImmutable();
        $task->setCreatedAt($createdAt);
        $this->assertSame($createdAt, $task->getCreatedAt());
    }

    public function testGetTitle(): void
    {
        $task = new Task();
        $this->assertNull($task->getTitle());
    }

    public function testSetTitle(): void
    {
        $task = new Task();
        $title = 'Test task';
        $task->setTitle($title);
        $this->assertSame($title, $task->getTitle());
    }

    public function testGetContent(): void
    {
        $task = new Task();
        $this->assertNull($task->getContent());
    }

    public function testSetContent(): void
    {
        $task = new Task();
        $content = 'Test content';
        $task->setContent($content);
        $this->assertSame($content, $task->getContent());
    }

    public function testIsDone(): void
    {
        $task = new Task();
        $this->assertFalse($task->isDone());
    }

    public function testToggle(): void
    {
        $task = new Task();
        $task->toggle(true);
        $this->assertTrue($task->isDone());
        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }

    public function testGetUser(): void
    {
        $task = new Task();
        $this->assertNull($task->getUser());
    }

    public function testSetUser(): void
    {
        $task = new Task();
        $user = new User();
        $task->setUser($user);
        $this->assertSame($user, $task->getUser());
    }
}
