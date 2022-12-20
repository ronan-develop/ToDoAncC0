<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends \Doctrine\Bundle\FixturesBundle\Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin
            ->setRoles(["ROLE_ADMIN"])
            ->setUsername("Admin")
            ->setPassword($this->hasher->hashPassword($admin, '0000'))
            ->setEmail(strtolower($admin->getUsername()) . "@mail.fr")
        ;

        $manager->persist($admin);

        for ($i=0; $i<10; $i++) {
            $user = new User();
            $user
                ->setUsername("user-".$i)
                ->setPassword($this->hasher->hashPassword($user, '0000'))
                ->setEmail(strtolower($user->getUsername()) . "@mail.fr")
                ->setRoles(["ROLE_USER"])
            ;

            $manager->persist($user);
        }

        $manager->flush();
    }
}