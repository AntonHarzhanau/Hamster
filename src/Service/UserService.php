<?php

namespace App\Service;

use App\DTO\CreateUserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private HamsterService $hamsterService,
        private EntityManagerInterface $em,
    ) {
    }

    public function createUser(CreateUserDTO $dto): User
    {

        $faker = \Faker\Factory::create();
        $user = $this->userRepository->findOneBy(['email' => $dto->email]);
        if ($user) {
            throw new \Exception('User already exists');
        }

        $user = new User();
        $user->setEmail($dto->email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);
        $user->setGold(500);

        $user->addHamster($this->hamsterService->createHamster($user, 'M'));
        $user->addHamster($this->hamsterService->createHamster($user, 'M'));
        $user->addHamster($this->hamsterService->createHamster($user, 'F'));
        $user->addHamster($this->hamsterService->createHamster($user, 'F'));
        
        return $user;
    }

    public function deleteUser(int $id): void
    {
        $user = $this->userRepository->find($id);
        $this->em->remove($user);
        $this->em->flush();
    }

}
