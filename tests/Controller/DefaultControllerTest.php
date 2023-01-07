<?php

namespace App\Tests\Controller;

use Exception;
use App\Entity\User;
use App\Tests\HelperTestCase;
use App\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends HelperTestCase
{
    private Router|null $urlGenerator;
    private KernelBrowser $client;
    private User $user;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router');
        $userRepo = $this->getEntityManager()->getRepository(User::class);
        $this->user = $userRepo->find(2);
    }

    /**
     * @throws Exception
     * @covers \App\Controller\DefaultController::index
     */
    public function testFailedAccessHomepageByLoginForm(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('homepage')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists("h1", "Se connecter");

        $this->client->submitForm("Se connecter", [
            '_username' => 'fail',
            '_password' => 'fail'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger', '.Identifiants invalides.');

    }

    /**
     * @return void
     */
    public function testPassAccessHomepageByLoginForm(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('homepage')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists("h1", "Se connecter");

        $this->client->submitForm("Se connecter", [
            '_username' => $this->user->getUsername(),
            '_password' => '0000'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorExists(
        'h1',
        'Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !'
        );
    }

    /**
     * @return void
     * @covers \App\Controller\DefaultController::index
     */
    public function testIndex(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('homepage')
        );
        $this->assertResponseRedirects('http://localhost/login');
        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode())
        ;
        $this->client->followRedirect();

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('login')
        );
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    /**
     * @covers \App\Controller\DefaultController::index
     * @throws Exception
     */
    public function testCanAccessHomepageWhenConnected(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $userRepo = $this->getEntityManager()->getRepository(User::class);
        $userRepo->find(1);
        /** @phpstan-ignore-next-line */
        $client->loginUser($this->user, 'secured_area');

        $crawler = $client->request(
            Request::METHOD_GET,
            /** @phpstan-ignore-next-line */
            $this->urlGenerator->generate('homepage')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        if(!$this->user) {
            $this->assertResponseRedirects('login',Response::HTTP_FOUND);
        }
    }

    /**
     * @return void
     */
    public function testNotLoggedHomepage(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertSelectorExists('label', 'Mot de passe');
    }

    /**
     * @return void
     */
    public function testAccessWhenUserLoggedIn(): void
    {
        $this->client->loginUser($this->user, 'secured_area');
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('homepage')
        );

        $this->assertSelectorExists('h1',
            'Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !'
        );
    }
}
