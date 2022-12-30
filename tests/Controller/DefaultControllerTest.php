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
        // simulation d'une request dans symfony
        $client = static::createClient();
        $client->request(Request::METHOD_GET, "/");

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