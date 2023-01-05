<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

/**
 * @covers \App\Controller\SecurityController::login
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\SecurityController::login
     * @dataProvider providePublicUri
     * @param string $uri
     * @return void
     * @throws Exception
     */
    public function testUserNotLoggedInAccessToLogin(string $uri): void
    {
        self::ensureKernelShutdown();
        $client = $this->createClient();
        $urlGenerator = $this->getContainer()->get('router');

        $client->request(Request::METHOD_GET, $uri);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertRouteSame('login');
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
        $client = $this->createClient();
        $urlGenerator = $this->getContainer()->get('router');

        $client->request(Request::METHOD_GET, $uri);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertRouteSame('login');
    }

    /**
     * @covers \App\Controller\SecurityController::login
     * @throws Exception
     */
    public function testUserCanLoginWithForm(): void
    {
        self::ensureKernelShutdown();
        $client = $this->createClient();
        $urlGenerator = $this->getContainer()->get('router');

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('login')
        );

        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        $form = $buttonCrawlerNode->form();

        $client->submit($form, [
            "_username" => "Admin",
            "_password" => "0000"
        ]);

        $client->followRedirect();
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
        $client = $this->createClient();
        $crawler = $client->request('GET', '/login');

        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        $form = $buttonCrawlerNode->form();

        $client->submit($form, [
            "_username" => "Toto",
            "_password" => "8888"
        ]);
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();
        $this->assertSelectorExists('html .alert-danger');
        $this->assertSelectorTextContains(".alert-danger", "Identifiants invalides.");
    }

    /**
     * @throws Exception
     */
    public function testLogout(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $session = new Session(new MockFileSessionStorage());
        $user = $userRepository->findOneBy(["username"=>"Admin"]);
        $client->loginUser($user);
        $client->request('GET', '/logout');
        $this->assertEquals(null, $session->get('user'));
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();
        $this->assertSelectorTextContains("h1", "Connexion");
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAnotherLogout()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Admin',
            'PHP_AUTH_PW'   => '0000'
        ]);
        $client->request('GET', '/logout');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
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
