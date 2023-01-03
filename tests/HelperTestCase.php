<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class HelperTestCase extends WebTestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * php -dxdebug.mode=coverage bin/phpunit --coverage-clover='reports/coverage/coverage.xml' --coverage-html='reports/coverage'
     * @throws Exception
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    /**
     * @throws Exception
     */
    protected function sessionStart(): Session
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();

        return $session;
    }

    /**
     * @throws Exception
     */
    protected function setUserNullInSession(): Session
    {
        $this->sessionStart()->set('user', null);
        return $this->sessionStart();
    }

    /**
     * @throws Exception
     */
    protected function  setUserInSession(): Session
    {
        $user = $userRepo = static::getContainer()->get(UserRepository::class);        $user = $userRepo->findOneBy([]);
        $this->sessionStart()->set('user', $user);
        return $this->sessionStart();
    }

    protected function fillLoginFormAsAdmin(Crawler $crawler): Form
    {
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');

        $form = $buttonCrawlerNode->form();
        $form['_username'] = "Admin";
        $form['_password'] = "0000";

        return $form;
    }

    /**
     * @throws Exception
     */
    protected function fillLoginFormAsUser(Crawler $crawler): Form
    {
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy([]);

        $form = $buttonCrawlerNode->form();
        $form['_username'] = $user;
        $form['_password'] = "0000";
        return $form;
    }

    protected function connectAdmin(KernelBrowser $client, Crawler $crawler): Crawler
    {
        return $client->submit(
            $this->fillLoginFormAsAdmin($crawler)
        );
    }

    /**
     * @throws Exception
     */
    protected function connectUser(KernelBrowser $client, Crawler $crawler): Crawler
    {
        return $client->submit(
            $this->fillLoginFormAsUser($crawler)
        );
    }
}