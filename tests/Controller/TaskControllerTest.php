<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Exception;
use App\Entity\Task;
use App\Tests\HelperTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

class TaskControllerTest extends HelperTestCase
{
    /**
     * @covers \App\Controller\TaskController::list
     * @throws Exception
     */
    public function testUserCannotListTaskWhileIsNotConnected()
    {
        $client = static::createClient();
        $urlGenerator = $this->getContainer()->get('router');

        $this->setUserNullInSession();
        $client->request(Request::METHOD_GET, $urlGenerator->generate('task_list'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    /**
     * @covers \App\Controller\TaskController::list
     * @uses \App\Controller\SecurityController::login
     * @throws Exception
     */
    public function testUserCanListTaskFromHomepage()
    {
        $this->setUserNullInSession();

        $client = static::createClient();
        $crawler =  $client->request('GET', '/');

        $this->assertResponseRedirects('http://localhost/login');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');

        $this->connectUser($client, $crawler);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertResponseIsSuccessful(Response::HTTP_OK);
        $this->assertSelectorTextContains(
            "h1",
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !"
        );

        $client->clickLink("Consulter la liste des tâches à faire");
        $this->assertResponseIsSuccessful();
        $this->assertSame("http://localhost/tasks", $client->getCrawler()->getBaseHref());
        $this->assertSame("http://localhost/tasks", $client->getCrawler()->getUri());
    }

    /**
     * @covers \App\Controller\TaskController::create
     * @uses \App\Form\TaskType
     * @throws Exception
     */
    public function testUserCanCreateTask(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');
        $client->followRedirects();

        $user = $this->getEntityManager()->getRepository(User::class)->find(2);
        $client->loginUser($user);

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("task_create")
        );
        $this->assertResponseStatusCodeSame(200);

        $client->submitForm('Ajouter', [
            'task[title]' => 'test ajout',
            'task[content]' => 'content de test ajout',
        ]);

        $task = $this->getEntityManager()->getRepository(Task::class)->findOneBy([
            'title' => 'test ajout'
        ]);
        $this->assertNotNull($task);
        $this->assertResponseIsSuccessful();
    }

    /**
     * @throws Exception
     */
    public function testUserCanAccessToEditTask(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');
        $taskRepository = $this->getEntityManager()->getRepository(Task::class);
        $task = $taskRepository->findOneBy([]);
        $id = $task->getId();

        $user = $task->getUser();
        $client->loginUser($user);

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('task_edit',[
                'id' => $id
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="task"]');
        $this->assertSelectorExists('input[id="task_title"]');
        $this->assertSelectorExists('textarea[id="task_content"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

    /**
     * @covers \App\Controller\TaskController::edit
     * @uses \App\Form\TaskType
     * @throws Exception
     */
    public function testTaskCanBeEdited(): void
    {
        $client = $this->createClient();
        $urlGenerator = $client->getContainer()->get('router');
        $taskRepo = $this->getEntityManager()->getRepository(Task::class);

        $task = $taskRepo->find(1);
        $id = $task->getId();
        $author = $task->getUser();
        $client->loginUser($author);

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('task_edit',[
                'id' => $id
            ])
        );

        $client->submitForm('Modifier', [
            'task[title]' => 'Titre Modifié',
            'task[content]' => 'Description modifiée',
        ]);

        $editedTask = $taskRepo->find(1);
        $this->assertNotNull($editedTask);
        $this->assertSame($author->getUsername(), $editedTask->getUser()->getUsername());
        $this->assertSame("Titre Modifié", $editedTask->getTitle());
        $this->assertSame("Description modifiée", $editedTask->getContent());
        $this->assertResponseRedirects('/tasks', 303);
    }

    /**
     * @covers \App\Controller\TaskController::toggleTask
     * @throws Exception
     */
    public function testUserCanToggleTask(): void
    {
        $client = static::createClient();
        /** @var Router $urlGenerator */
        $urlGenerator = $client->getContainer()->get('router');
        $taskRepository =$this->getEntityManager()->getRepository(Task::class);

        $task = $taskRepository->findOneBy(["isDone" => false]);
        $id = $task->getId();
        $author = $task->getUser();
        $initialStatus = $task->isDone();
        $this->assertIsBool($initialStatus);
        $client->loginUser($author);
        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("task_toggle", [
                'id' => $id
            ])
        );
        $statusAfter = $task->isDone();
        $this->assertIsBool($statusAfter);
        $this->assertNotSame($statusAfter, $initialStatus);
        $this->assertResponseRedirects("/tasks", 303);
    }

    /**
     * @covers \App\Controller\TaskController::deleteTask
     * @throws Exception
     */
    public function testOwnerCanDeleteTask(): void
    {
        $client = static::createClient();

        /** @var Router $urlGenerator */
        $urlGenerator = $client->getContainer()->get('router');

        $taskRepo = $this->getEntityManager()->getRepository(Task::class);
        $task = $taskRepo->find(1);
        $taskId = $task->getId();
        $author = $task->getUser();
        $client->loginUser($author);

        $client->request(
            Request::METHOD_DELETE,
            $urlGenerator->generate("task_delete", [
                "id" => $taskId
            ])
        );
        $this->assertRouteSame('task_delete');
        dd($client->getResponse());

        $this->assertResponseStatusCodeSame(302);
        $this->assertNull($taskRepo->find(1));
    }

    /**
     * @covers \App\Controller\TaskController::deleteTask
     * @throws Exception
     */
    public function testCannotDeleteTAskIfNotOwner()
    {
        $client = $this->createClient();
        $taskRepo = $this->getEntityManager()->getRepository(Task::class);

        /** @var Router $urlGenerator */
        $urlGenerator = $client->getContainer()->get('router');

        // reference
        $task = $taskRepo->find(2);
        $taskId = $task->getId();
        $author = $task->getUser();

        // task with another user
        $taskWithAnotherUser = $taskRepo->createQueryBuilder("t")
            ->where('NOT t.user = :user')
            ->setParameter('user', $author)
            ->setFirstResult(1)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
        // the other user
        $anotherAuthor = $taskWithAnotherUser[0]->getUser();
        $this->assertNotSame($author, $anotherAuthor);

        $client->loginUser($anotherAuthor);
        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("task_delete",[
                'id' => $taskId
            ])
        );
        $client->followRedirect();

    }
}