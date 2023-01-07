<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

/**
 * @covers \App\Controller\SecurityController::login
 */
class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Router|null $urlGenerator;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router');
    }

    /**
     * @covers \App\Controller\SecurityController::login
     * @dataProvider providePublicUri
     * @param string $uri
     * @return void
     * @throws Exception
     */
    public function testUserNotLoggedInAccessToLogin(string $uri): void
    {
        $session = new Session(new MockArraySessionStorage());
        $this->assertEquals(null, $session->get('user'));

        $this->client->request(Request::METHOD_GET, $uri);
        $this->assertSelectorTextContains('h1', 'Connexion');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @covers \App\Controller\SecurityController::login
     * @dataProvider providePrivateUri
     * @param string $uri
     * @return void
     * @throws Exception
     */
    public function testUserNotLoggedInCannotAccessAnyOtherRoute(string $uri): void
    {
        self::ensureKernelShutdown();
        $session = new Session(new MockArraySessionStorage());
        $this->assertEquals(null, $session->get('user'));

        $this->client->request(Request::METHOD_GET, $uri);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    /**
     * @covers \App\Controller\SecurityController::login
     * @throws Exception
     */
    public function testUserCanLoginWithForm(): void
    {
        self::ensureKernelShutdown();

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('login')
        );

        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        $form = $buttonCrawlerNode->form();

        $this->client->submit($form, [
            "_username" => "Admin",
            "_password" => "0000"
        ]);

        $this->client->followRedirect();
        $this->assertSelectorTextContains(
            "h1",
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !"
        );
    }

    /**
     * @covers \App\Controller\SecurityController::login
     */
    public function testUserCannotLogin(): void
    {
        self::ensureKernelShutdown();

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('login')
        );

        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        $form = $buttonCrawlerNode->form();

        $this->client->submit($form, [
            "_username" => "Toto",
            "_password" => "8888"
        ]);
        $this->assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        $this->assertSelectorExists('html .alert-danger');
        $this->assertSelectorTextContains(".alert-danger", "Identifiants invalides.");
    }

    /**
     * @throws Exception
     */
    public function testLogout(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('logout')
        );
        $this->assertEquals(null, $session->get('user'));
        $this->assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        $this->assertSelectorTextContains("h1", "Connexion");
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAnotherLogout()
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('logout')
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('label', 'Mot de passe');
    }

    /**
     * @return Generator
     */
    public function providePrivateUri(): Generator
    {
        yield ['/'];
        yield ['/tasks'];
        yield ['/tasks/create'];
        yield ['/users'];
        yield ['/users/create'];
    }

    /**
     * @return Generator
     */
    protected function providePublicUri(): Generator
    {
        yield ['/login'];
    }
}
