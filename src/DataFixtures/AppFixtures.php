<?php

namespace App\DataFixtures;

use App\DTO\CreateUserDTO;
use App\Entity\User;
use App\Service\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserService $userService,
    ) {
    }
    public function load(ObjectManager $manager): void
    {

        $conn = $manager->getConnection();
        $conn->executeStatement('TRUNCATE TABLE "user", hamster RESTART IDENTITY CASCADE');
        
        

        $adminData = new CreateUserDTO();
        $adminData->email = "admin@mail.com";
        $adminData->password = "1234";

        $admin = $this->userService->createUser($adminData);
        $admin->setRoles(["ROLE_ADMIN"]);
        $manager->persist($admin);
    
        $userData = new CreateUserDTO();
        $userData->email = "user@mail.com";
        $userData->password = "1234";

        $user = $this->userService->createUser($userData);
        $manager->persist($user);

        $manager->flush();
    }

}
