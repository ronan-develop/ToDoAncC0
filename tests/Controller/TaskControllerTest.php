<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use Exception;
use App\Entity\User;
use App\Entity\Task;
use App\Tests\HelperTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends HelperTestCase
{
    private KernelBrowser $client;
    private Router|null $urlGenerator;
    private User $user;
    private Task $task;
    private TaskRepository $taskRepo;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->taskRepo = $this->getEntityManager()->getRepository(Task::class);
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
        $userRepo = $this->getEntityManager()->getRepository(User::class);
        $this->user = $userRepo->find(2);
        $this->task = $this->taskRepo->findOneBy([]);
    }

    /**
     * @covers \App\Controller\TaskController::list
     * @throws Exception
     */
    public function testUserCannotListTaskWhileIsNotConnected()
    {
        $this->setUserNullInSession();
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_list'));
        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );

        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    /**
     * @covers \App\Controller\TaskController::list
     * @throws Exception
     * @uses   \App\Controller\SecurityController::login
     */
    public function testUserCanListTaskFromHomepage()
    {

        $this->client->loginUser($this->user, 'secured_area');
        $this->client->request(Request::METHOD_GET, '/');
        $this->assertResponseIsSuccessful();

        $this->client->clickLink("Consulter la liste des tâches à faire");
        $this->assertResponseIsSuccessful();
        $this->assertSame("http://localhost/tasks", $this->client->getCrawler()->getBaseHref());
        $this->assertSame("http://localhost/tasks", $this->client->getCrawler()->getUri());
    }

    /**
     * @covers \App\Controller\TaskController::create
     * @throws Exception
     * @uses   \App\Form\TaskType
     */
    public function testNotAuthenticatedUserAccessTaskCreate(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate("task_create"),
        );
        $crawler = $this->client->followRedirect();
        $this->assertRouteSame("login");

        $form = $crawler->selectButton("Se connecter")->form([
            '_username' => 'Admin',
            '_password' => '0000'
        ]);
        $this->client->submit($form);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );
        $this->client->followRedirect();

        $this->assertRouteSame('task_create');

    }

    /**
     * @covers \App\Controller\TaskController::create
     * @throws Exception
     */
    public function testAuthenticatedUserAccessTaskCreate()
    {
        $this->client->loginUser($this->user, 'secured_area');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_create')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @covers \App\Controller\TaskController::create
     * @throws Exception
     */
    public function testCreateTask()
    {
        $this->client->loginUser($this->user, 'secured_area');
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_create'));

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'titre test création',
            'task[content]' => 'content test création'
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects(
            $this->urlGenerator->generate('task_list')
        );
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @covers \App\Controller\TaskController::edit
     * @throws Exception
     */
    public function testUserCanAccessToEditTask(): void
    {
        $id = $this->task->getId();

        $this->user = $this->task->getUser();
        $this->client->loginUser($this->user, "secured_area");

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_edit', [
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
        $id = $this->task->getId();
        $author = $this->task->getUser();
        $this->client->loginUser($author, 'secured_area');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_edit', [
                'id' => $id
            ])
        );

        $this->client->submitForm('Modifier', [
            'task[title]' => 'Titre Modifié',
            'task[content]' => 'Description modifiée',
        ]);

        $this->client->followRedirect();

        $this->assertNotNull($this->taskRepo->find($id));
        $editedTask = $this->taskRepo->find($id);

        $this->assertSame($author->getUserIdentifier(), $editedTask->getUser()->getUserIdentifier());
        $this->assertSame("Titre Modifié", $editedTask->getTitle());
        $this->assertSame("Description modifiée", $editedTask->getContent());
    }

    /**
     * @covers \App\Controller\TaskController::toggleTask
     * @throws Exception
     */
    public function testUserCanToggleTask(): void
    {

        $task = $this->taskRepo->findOneBy(["isDone" => false]);
        $id = $task->getId();
        $author = $task->getUser();
        $initialStatus = $task->isDone();
        $this->assertIsBool($initialStatus);
        $this->client->loginUser($author, 'secured_area');
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate("task_toggle", [
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
        $task = $this->taskRepo->find(1);
        $taskId = $task->getId();
        $author = $task->getUser();
        $this->client->loginUser($author, 'secured_area');

        $this->client->request(
            Request::METHOD_DELETE,
            $this->urlGenerator->generate("task_delete", [
                "id" => $taskId
            ])
        );
        $this->assertRouteSame('task_delete');

        $this->assertResponseStatusCodeSame(303);
        $this->assertNull($this->taskRepo->find(1));
    }

    /**
     * @covers \App\Controller\TaskController::deleteTask
     * @throws Exception
     */
    public function testCannotDeleteTAakIfNotOwner()
    {
        // reference
        $task = $this->taskRepo->find(2);
        $taskId = $task->getId();
        $author = $task->getUser();

        // task with another user
        // demande au queryBuilder de créer une requête
        $taskWithAnotherUser = $this->taskRepo->createQueryBuilder("t")
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
            // Hydrate l'objet
            ->getResult();

        // the other user
        $anotherAuthor = $taskWithAnotherUser[0]->getUser();
        $this->assertNotSame($author, $anotherAuthor);

        $this->client->loginUser($anotherAuthor, 'secured_area');
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate("task_delete", [
                'id' => $taskId
            ])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}