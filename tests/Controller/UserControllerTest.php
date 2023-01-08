<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\HelperTestCase;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class UserControllerTest extends HelperTestCase
{
    private KernelBrowser $client;
    private Router|null $urlGenerator;
    private User $admin;
    private User $user;
    private UserRepository $userRepo;

    /**
     * @throws Exception
     */public function setUp(): void
    {
        $this->client = static::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router');
        $this->userRepo = $this->getEntityManager()->getRepository(User::class);
        $this->admin = $this->userRepo->findOneBy(["username"=>"Admin"]);
        $this->user = $this->userRepo->find(2);
    }

    /**
     * @covers \App\Controller\UserController::list
     * @throws Exception
     */
    public function testAdminCanListUserWhenNotLogged(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('user_list')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists("h1", "Se connecter");

        $this->client->submitForm("Se connecter", [
            '_username' => $this->admin->getUsername(),
            '_password' => '0000'
        ]);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful(Response::HTTP_OK);

        $this->assertSelectorTextContains(
            "h1",
            "Liste des utilisateurs"
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame("http://localhost/users", $this->client->getCrawler()->getBaseHref());
        $this->assertSame("http://localhost/users", $this->client->getCrawler()->getUri());
    }

    /**
     * @covers \App\Controller\UserController::list
     * @throws Exception
     */
    public function testAdminCanListUserWhenLogged()
    {
        $this->client->loginUser($this->admin, 'secured_area');
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate("user_list")
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    /**
     * @throws Exception
     */
    public function testRedirectToLoginIfNotLogged()
    {
        $this->setUserNullInSession();
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('user_list')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertRouteSame('login');
    }

    /**
     * @throws Exception
     */
    public function testUserCannotAccess()
    {
        $this->client->loginUser($this->user, 'secured_area');
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('user_list')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanAccessFormToCreateUser()
    {
        $this->client->loginUser($this->admin, 'secured_area');
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate("user_create")
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
    }

    /**
     * @throws Exception
     */
    public function testAdminCanCreate(): void
    {
        $this->client->loginUser($this->admin, 'secured_area');
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate("user_create")
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');
        $this->assertSelectorExists('input[id="user_username"]');

        $newUser = $this->client->submitForm('Ajouter', [
            "user[username]" => "test",
            "user[password][first]" => "0000",
            "user[password][second]" => "0000",
            "user[email]" => "test@domain.fr",
            "user[roles]" => "ROLE_USER"
        ]);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert-success');
        $this->assertEquals("test", $this->userRepo->findOneBy(['username'=>'test'])->getUserIdentifier());
    }

    /**
     * @throws Exception
     */
    public function testAdminAuthenticatedEditUser()
    {
        $this->client->loginUser($this->admin, 'secured_area');
        $userId = $this->user->getId();
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', [
            'id' => $userId
        ]));

        $this->client->submitForm('Modifier', [
            'user[username]' => 'toto',
            'user[email]' => 'toto@domain.fr',
            'user[roles]' => 'ROLE_USER',
        ]);

        $this->assertResponseRedirects('/users', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert-success');
    }
}