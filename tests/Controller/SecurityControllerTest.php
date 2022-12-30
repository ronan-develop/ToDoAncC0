<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $this->assertResponseStatusCodeSame(302);
        // @TODO: how can we test User Session to check that current user is indeed our test user
    }

    /**
     * @covers \App\Controller\SecurityController::login
     */
    public function loginFailWithWrongsCredentials(): void
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
    }
}
