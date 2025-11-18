<?php

namespace App\Service;

use App\Entity\Hamster;
use App\Entity\User;
use App\Repository\HamsterRepository;
use Doctrine\ORM\EntityManagerInterface;


class HamsterService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HamsterRepository $hamsterRepository,
    ) {
    }

    public function createHamster($owner, $gender): Hamster
    {
        $faker = \Faker\Factory::create();
        $name = $gender === 'M' ? $faker->firstNameMale : $faker->firstNameFemale;

        $hamster = new Hamster();
        $hamster->setName($name);
        $hamster->setAge(0);
        $hamster->setHunger(100);
        $hamster->setGender($gender);
        $hamster->setActive(true);
        $hamster->setOwner($owner);

        $this->entityManager->persist($hamster);
        $this->entityManager->flush();

        return $hamster;

    }

    public function getById(int $id, User $user, bool $isAdmin): ?Hamster
    {
        $hamster = $this->hamsterRepository->find($id);

        if (!$hamster) {
            return null;
        }

        if (!$isAdmin && $hamster->getOwner()->getId() !== $user->getId()) {
            return null;
        }

        return $hamster;
    }

    public function getAllByUser(User $user): array
    {
        return $this->hamsterRepository->findBy(['owner' => $user]);
    }

    public function reproduce(int $hamster1Id, int $hamster2Id, User $user): Hamster
    {
        $hamster1 = $this->hamsterRepository->find($hamster1Id);
        $hamster2 = $this->hamsterRepository->find($hamster2Id);

        if ($hamster1->getGender() === $hamster2->getGender()) {
            throw new \Exception('Hamsters must be of different genders to reproduce');
        }

        if ($hamster1->getOwner()->getId() !== $user->getId() || $hamster2->getOwner()->getId() !== $user->getId()) {
            throw new \Exception('You can only reproduce your own hamsters');
        }

        if (!$hamster1->isActive() || !$hamster2->isActive()) {
            throw new \Exception('Both hamsters must be active to reproduce');
        }

        $humsters = $user->getHamsters();

        foreach ($humsters as $hamster) {
            $this->action($hamster);
        }

        $faker = \Faker\Factory::create();
        $gender = $faker->randomElement(['M', 'F']);
        $name = $gender === 'M' ? $faker->firstNameMale : $faker->firstNameFemale;
        $babyHamster = new Hamster();
        $babyHamster->setName($name);
        $babyHamster->setAge(0);
        $babyHamster->setHunger(100);
        $babyHamster->setGender($gender);
        $babyHamster->setActive(true);
        $babyHamster->setOwner($user);
        $this->entityManager->persist($babyHamster);
        $this->entityManager->flush();



        return $babyHamster;
    }


    public function feed(int $hamsterId, User $user): int
    {
        $hamster = $this->hamsterRepository->find($hamsterId);
        $cost = 100 - $hamster->getHunger();
        $user->setGold($user->getGold() - $cost);

        $humsters = $user->getHamsters();

        foreach ($humsters as $hamster) {
            $this->action($hamster);
        }

        $hamster->setHunger(100);

        $this->entityManager->flush();

        return $user->getGold();
    }

    public function sell(int $hamsterId, User $user): void
    {
        $hamster = $this->hamsterRepository->find($hamsterId);
        $salePrice = 300;
        $user->setGold($user->getGold() + $salePrice);
        $this->entityManager->remove($hamster);

        $humsters = $user->getHamsters();

        foreach ($humsters as $hamster) {
            $this->action($hamster);
        }

        $this->entityManager->flush();
    }

    public function sleep(int $nbDays, User $user): void
    {
        $humsters = $user->getHamsters();

        foreach ($humsters as $hamster) {
            for ($i = 0; $i < $nbDays; $i++) {
                $this->action($hamster);
            }
        }

        $this->entityManager->flush();
    }

    public function rename(int $hamsterId, string $newName, User $user): Hamster
    {
        $hamster = $this->hamsterRepository->find($hamsterId);

        if ($hamster->getOwner()->getId() !== $user->getId()) {
            throw new \Exception('You can only rename your own hamsters');
        }

        $hamster->setName($newName);

        $this->entityManager->flush();

        return $hamster;
    }

    private function action(Hamster $hamster): void
    {
        if ($hamster->getHunger() > 0) {
            $hamster->setHunger($hamster->getHunger() - 5);
            return;
        }

        if ($hamster->getAge() < 500) {
            $hamster->setAge($hamster->getAge() + 5);
        }

        if ($hamster->getHunger() <= 0 || $hamster->getAge() >= 500) {
            $hamster->setActive(false);
        }

        $this->entityManager->persist($hamster);
        $this->entityManager->flush();
    }
}