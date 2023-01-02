<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

/**
 * @covers \App\Controller\SecurityController::login
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\SecurityController::login
     */
    public function testUserCanAccessLoginForm():void
    {
//        Shuts the kernel down if it was used in the test - called by the tearDown method by default.
        self::ensureKernelShutdown();
        $client = $this->createClient();
        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Controller\SecurityController::login
     */
    public function testUserCanLoginWithForm(): void
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/login');

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
    public function testLogout()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $session = new Session(new MockFileSessionStorage());
        $user = $userRepository->findOneBy([]);
        $client->loginUser($user);
        $client->request('GET', '/logout');
        $this->assertEquals(null, $session->get('user'));
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();
        $this->assertSelectorTextContains("h1", "Connexion");
        $this->assertResponseStatusCodeSame(200);
    }
}
