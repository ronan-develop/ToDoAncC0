<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Exception;
use App\Entity\Task;
use App\Tests\HelperTestCase;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

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
        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    /**
     * @covers \App\Controller\TaskController::list
     * @throws Exception
     * @uses   \App\Controller\SecurityController::login
     */
    public function testUserCanListTaskFromHomepage()
    {
        $this->setUserNullInSession();

        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseRedirects('http://localhost/login');

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();

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
     * @throws Exception
     * @uses   \App\Form\TaskType
     */
    public function testNotAuthenticatedUserAccessTaskCreate(): void
    {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("task_create"),
        );
        $crawler = $client->followRedirect();
        $this->assertRouteSame("login");

        $form = $crawler->selectButton("Se connecter")->form([
            '_username' => 'Admin',
            '_password' => '0000'
        ]);
        $client->submit($form);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $client->getResponse()->getStatusCode(),
            $client->getResponse()->getContent()
        );
        $crawler = $client->followRedirect();

        $this->assertRouteSame('task_create');

    }

    /**
     * @covers \App\Controller\TaskController::create
     * @throws Exception
     */
    public function testAuthenticatedUserAccessTaskCreate()
    {
        $client = static::createClient();

        $urlGenerator = $this->getContainer()->get('router');
        $userRepository = $this->getEntityManager()->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->find(2);

        $client->loginUser($user, 'secured_area');

        $client->request(Request::METHOD_GET, $urlGenerator->generate('task_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @covers \App\Controller\TaskController::create
     * @throws Exception
     */
    public function testCreateTask()
    {
        $client = static::createClient();
        $urlGenerator = $this->getContainer()->get('router');
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $taskRepository = $this->getEntityManager()->getRepository(Task::class);

        /** @var User $user */
        $user = $userRepository->find(3);
        $name = $user->getId();

        $client->loginUser($user, 'secured_area');
        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate('task_create'));

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'titre test création',
            'task[content]' => 'content test création'
        ]);

        $client->submit($form);
        $this->assertResponseRedirects(
            $urlGenerator->generate('task_list')
        );
        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @covers \App\Controller\TaskController::edit
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
        $client->loginUser($user, "secured_area");

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('task_edit', [
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
     * @throws Exception
     * @uses   \App\Form\TaskType
     */
    public function testTaskCanBeEdited(): void
    {
        $client = $this->createClient();
        $urlGenerator = $client->getContainer()->get('router');
        $taskRepo = $this->getEntityManager()->getRepository(Task::class);

        $task = $taskRepo->find(1);
        $id = $task->getId();
        $author = $task->getUser();
        $client->loginUser($author, 'secured_area');

        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('task_edit', [
                'id' => $id
            ])
        );

        $client->submitForm('Modifier', [
            'task[title]' => 'Titre Modifié',
            'task[content]' => 'Description modifiée',
        ]);

        $editedTask = $taskRepo->find(1);
        $this->assertNotNull($editedTask);
        $this->assertSame($author->getUserIdentifier(), $editedTask->getUser()->getUserIdentifier());
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
        $taskRepository = $this->getEntityManager()->getRepository(Task::class);

        $task = $taskRepository->findOneBy(["isDone" => false]);
        $id = $task->getId();
        $author = $task->getUser();
        $initialStatus = $task->isDone();
        $this->assertIsBool($initialStatus);
        $client->loginUser($author, 'secured_area');
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
        $client->loginUser($author, 'secured_area');

        $client->request(
            Request::METHOD_DELETE,
            $urlGenerator->generate("task_delete", [
                "id" => $taskId
            ])
        );
        $this->assertRouteSame('task_delete');

        $this->assertResponseStatusCodeSame(303);
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
        // demande au queryBuilder de créer une requête
        $taskWithAnotherUser = $taskRepo->createQueryBuilder("t")
            // ou le t.user sera !== du user que je vais te passer en paramètre
            ->where('NOT t.user = :user')
            // je set les parameters
            ->setParameter('user', $author)
            // Définit la position du premier résultat à récupérer
            ->setFirstResult(1)
            // limit
            ->setMaxResults(1)
            // construit la requête
            ->getQuery()
            // hydrate l'objet
            ->getResult();

        // the other user
        $anotherAuthor = $taskWithAnotherUser[0]->getUser();
        $this->assertNotSame($author, $anotherAuthor);

        $client->loginUser($anotherAuthor, 'secured_area');
        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("task_delete", [
                'id' => $taskId
            ])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}