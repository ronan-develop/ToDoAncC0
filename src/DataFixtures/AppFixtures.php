<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $admin = new User();
        $admin->setEmail($faker->email())
            ->setUsername($faker->userName())
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword($this->userPasswordHasher->hashPassword(
                $admin,
                "0000"
            ));
        $manager->persist($admin);
        for ($i = 0; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email())
                ->setUsername($faker->userName())
                ->setPassword($this->userPasswordHasher->hashPassword(
                    $user,
                    "0000"
                ));
            $manager->persist($user);

            for ($t=0;$t<=10;$t++){
                $task = new Task();
                $task->setUser($user)
                    ->setTitle($faker->words(random_int(1, 4), true))
                    ->setContent($faker->sentences(random_int(1,4), true))
                    ->setCreatedAt(
                        (new \DateTimeImmutable())
                        ->setTime(mt_rand(0, 24), mt_rand(0, 60), mt_rand(0, 60))
                        ->setDate(mt_rand(2000, 2022), mt_rand(0, 12), mt_rand(0, 30))
                    )
                    ->toggle(rand(0, 1))
                ;
                $manager->persist($task);
            }
        }
        $manager->flush();
    }
}
