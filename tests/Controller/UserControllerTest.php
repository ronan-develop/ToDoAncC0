<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\HelperTestCase;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class UserControllerTest extends HelperTestCase
{
    /**
     * @covers \App\Controller\UserController::list
     * @throws Exception
     */
    public function testAdminCanListUserWhenNotLogged(): void
    {
        // make sure no user in session
        $this->setUserNullInSession();

        $client = static::createClient();
        $crawler =  $client->request('GET', '/users');

        $this->assertResponseRedirects('http://localhost/login');

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');

        $this->connectAdmin($client, $crawler);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertResponseIsSuccessful(Response::HTTP_OK);
        $this->assertSelectorTextContains(
            "h1",
            "Liste des utilisateurs"
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame("http://localhost/users", $client->getCrawler()->getBaseHref());
        $this->assertSame("http://localhost/users", $client->getCrawler()->getUri());
    }

    /**
     * @covers \App\Controller\UserController::list
     * @throws Exception
     */
    public function testAdminCanListUserWhenLogged()
    {
        $client = static::createClient();
        $urlGenerator = $this->getContainer()->get('router');
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $admin = $userRepository->findOneBy(["username" => "Admin"]);

        $client->loginUser($admin, 'secured_area');
        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("user_list")
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    /**
     * @throws Exception
     */
    public function testRedirectToLoginIfNotLogged()
    {
        $client = static::createClient();
        $client->followRedirects();

        $urlGenerator = $client->getContainer()->get('router');
        $userRepo = $this->getEntityManager()->getRepository(User::class);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('user_list')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertRouteSame('login');
    }

    /**
     * @throws Exception
     */
    public function testUserCannotAccess()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->set('user', null);

        $client = static::createClient();
        $crawler =  $client->request('GET', '/users');

        $this->assertResponseRedirects('http://localhost/login');

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');

        $buttonCrawlerNode = $crawler->selectButton('Se connecter');

        $form = $buttonCrawlerNode->form();
        $form['_username'] = $this->getEntityManager()->getRepository(User::class)->find(2);
        $form['_password'] = "0000";

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

    }

    public function testAdminCanAccessFormToCreateUser()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->set('user', null);

        $client = static::createClient();
        $crawler =  $client->request('GET', '/users');

        $this->assertResponseRedirects('http://localhost/login');

        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');

        $buttonCrawlerNode = $crawler->selectButton('Se connecter');

        $form = $buttonCrawlerNode->form();
        $form['_username'] = "Admin";
        $form['_password'] = "0000";

        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful(Response::HTTP_OK);
        $this->assertSelectorTextContains(
            "h1", "Liste des utilisateurs"
        );
        $link = $crawler->selectLink("Créer un utilisateur")->link();
        $client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains("h1", "Créer un utilisateur");
    }

    /**
     * @throws Exception
     */
    public function testAdminCanCreate(): void
    {
        $client = static::createClient();
        $urlGenerator = $this->getContainer()->get('router');
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $admin = $userRepository->findOneBy(["username" => "Admin"]);

        $client->loginUser($admin, 'secured_area');
        $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate("user_create")
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');
        $this->assertSelectorExists('input[id="user_username"]');

        $newUser = $client->submitForm('Ajouter', [
            "user[username]" => "test",
            "user[password][first]" => "0000",
            "user[password][second]" => "0000",
            "user[email]" => "test@domain.fr",
            "user[roles]" => "ROLE_USER"
        ]);
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert-success');
        $this->assertEquals("test", $userRepository->findOneBy(['username'=>'test'])->getUserIdentifier());
    }

    /**
     * @throws Exception
     */
    public function testAdminAuthenticatedEditUser()
    {
        $client = static::createClient();
        $userRepo = $this->getEntityManager()->getRepository(User::class);
        $urlGenerator = $this->getContainer()->get('router');
        $admin = $userRepo->findOneBy(["username" => "Admin"]);
        $client->loginUser($admin, 'secured_area');

        $user = $userRepo->findOneBy(['username' => 'roux.nathalie']);
        $userId = $user->getId();
        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate('user_edit', [
            'id' => $userId
        ]));

        $client->submitForm('Modifier', [
            'user[username]' => 'toto',
            'user[email]' => 'toto@domain.fr',
            'user[roles]' => 'ROLE_USER',
        ]);

        $this->assertResponseRedirects('/users', Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert-success');
    }
}