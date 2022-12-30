<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultControllerTest
 * @package App\Tests
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testWhenUserNotLogged(): void
    {
        /**
         * Shuts the kernel down if it was used in the test
         */
        self::ensureKernelShutdown();
        // simulation d'une request dans symfony
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, "/");

        $this->assertSelectorTextContains('title', 'Redirecting to http://localhost/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /**
     * @return Generator
     */
    public function provideUri(): Generator
    {
        yield ['/'];
    }
}