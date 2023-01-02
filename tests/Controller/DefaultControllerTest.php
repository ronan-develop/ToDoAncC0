<?php

namespace App\Tests\Controller;

use Exception;
use App\Entity\User;
use App\Tests\HelperTestCase;
use App\Controller\DefaultController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class DefaultControllerTest extends HelperTestCase
{
    /**
     * @throws Exception
     * @covers \App\Controller\DefaultController::index
     */
    public function testAccessHomepageByLoginForm()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->set('user', null);

        $client = static::createClient();
        $crawler =  $client->request('GET', '/');

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
            "h1", "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !"
        );
    }

    /**
     * @return void
     * @covers \App\Controller\DefaultController::index
     */
    public function testIndex(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $session->set('user', null);

        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseRedirects('http://localhost/login');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    /**
     * @covers \App\Controller\DefaultController::index
     * @throws Exception
     */
    public function testCanAccessHomepageWhenConnected()
    {
        $client = static::createClient();
        $client->followRedirects();

        $urlGenerator = $client->getContainer()->get('router');
        $userRepo = $this->getEntityManager()->getRepository(User::class);
        $user = $userRepo->findOneBy([]);
        $client->loginUser($user);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('homepage')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        if(!$user) {
            $this->assertResponseRedirects('login',Response::HTTP_FOUND);
        }
    }
}
