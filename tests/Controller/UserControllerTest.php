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
     * @throws Exception
     */
    public function testAdminCanListUser(): void
    {
        // make sure no user in session
        $this->setUserNullInSession();

        $client = static::createClient();
        $crawler =  $client->request('GET', '/users');

        $this->assertResponseRedirects('http://localhost/login');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

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
     * @throws Exception
     */
    public function testRedirectIfNotLogged()
    {
        $client = static::createClient();
        $client->followRedirects();

        $urlGenerator = $client->getContainer()->get('router');
        $userRepo = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepo->findOneBy([]);
        $client->loginUser($user);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('user_list')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        if(!$user) {
            $this->assertResponseRedirects('login',Response::HTTP_FOUND);
        }
    }

    /**
     * @throws Exception
     */
    public function testAdminCanAccessListAfterLogin()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->set('user', null);

        $client = static::createClient();
        $crawler =  $client->request('GET', '/users');

        $this->assertResponseRedirects('http://localhost/login');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

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
        $client->followRedirect();
        $this->assertResponseIsSuccessful(Response::HTTP_OK);
        $this->assertSelectorTextContains(
            "h1", "Liste des utilisateurs"
        );
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

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

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

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

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
//    public function testAdminCanCreateUser()
//    {
//        $client = static::createClient();
//        $admin = $this->getEntityManager()->getRepository(User::class)->findOneBy(["username" => "Admin"]);
//        $client->loginUser($admin);
//        $crawler = $client->request(
//            Request::METHOD_GET, $this->getContainer()->get('router')->generate("user_create")
//        );
//        $client->followRedirect();
//        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
//        $form = $crawler->selectButton('Ajouter')->form([
//            'user[username]' => 'toto',
//            'user[roles]' => 'ROLE_USER',
//            'user[password][first]' => '0000',
//            'user[password][second]' => '0000',
//            'user[email]' => 'toto@domain.fr'
//        ]);
//        $client->submit($form);
//
//        $newUser = $this->getEntityManager()->getRepository(User::class)->findOneBy([
//            'username' => 'testUsername'
//        ]);
//
//        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
//        $this->assertSelectorExists('.alert-success');
//        $this->assertSelectorExists('label', 'Mot de passe');
//    }
}
