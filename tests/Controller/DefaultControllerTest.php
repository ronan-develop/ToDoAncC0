<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends WebTestCase
{
    public function testRedirectionToLoggingPageIfNotLogged()
    {
        // simulation d'une request dans symfony
        $client = static::createClient();
        $client->request(Request::METHOD_GET, "/");
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }
}