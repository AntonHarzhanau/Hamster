<?php

namespace App\Controller;

use App\Service\HamsterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
final class HamsterController extends AbstractController
{
    public function __construct(
        private HamsterService $hamsterService,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }
    #[Route('/hamsters', name: 'get_hamsters', methods: ['GET'], format: 'json')]
    public function getAll(): JsonResponse
    {
        $user = $this->getUser();
        $hamsters = $this->hamsterService->getAllByUser($user);

        return $this->json(
            $hamsters,
            Response::HTTP_OK,
            [],
            ['groups' => 'hamster:read']
        );
    }

    #[Route('/hamsters/{id}', name: 'get_hamster', methods: ['GET'], format: 'json')]
    public function getById($id): JsonResponse
    {
        $user = $this->getUser();
        $hasAccess = $this->isGranted('ROLE_ADMIN');
        $humster = null;

        $humster = $this->hamsterService->getById($id, $user, $hasAccess);

        if (!$humster) {
            return $this->json([
                'message' => 'Hamster not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            $humster
        ], Response::HTTP_OK, [], ['groups' => 'hamster:read']);
    }

    #[Route('/hamsters/reproduce', name: 'hamsters_reproduce', methods: ['POST'], format: 'json')]
    public function reproduce(Request $request): JsonResponse
    {

        $user = $this->getUser();

        $payload = json_decode($request->getContent(), true);

        $bady = $this->hamsterService->reproduce($payload['idHamster1'], $payload['idHamster2'], $user);

        return $this->json(
            $bady,
            Response::HTTP_OK,
            [],
            ['groups' => 'hamster:read']
        );
    }

    #[Route('/hamsters/{id}/feed', name: 'hamsters_feed', methods: ['POST'], format: 'json')]
    public function feed($id): JsonResponse
    {
        $user = $this->getUser();
        $money = $this->hamsterService->feed($id, $user);
        return $this->json([
            'user_gold' => $money,
        ], Response::HTTP_OK);
    }

    #[Route('/hamsters/{id}/sell', name: 'hamsters_sell', methods: ['POST'], format: 'json')]
    public function sell($id): JsonResponse
    {
        $user = $this->getUser();
        $this->hamsterService->sell($id, $user);
        return $this->json([
            'message' => 'Hamster sold successfully',
        ], Response::HTTP_OK);
    }

    #[Route('/hamsters/sleep/{nbDays}', name: 'hamsters_sleep', methods: ['POST'], format: 'json')]
    public function sleep(int $nbDays): JsonResponse
    {
        $user = $this->getUser();

        $this->hamsterService->sleep($nbDays, $user);

        return $this->json(['message' => 'Hamsters have slept'], Response::HTTP_OK);
    }

    #[Route('/hamsters/{id}/rename ', name: 'hamsters_rename', methods: ['PUT'], format: 'json')]
    public function rename(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        dd($data);

        $humster = $this->hamsterService->rename($id, $data, $user);
        return $this->json($humster, Response::HTTP_OK, [], ['groups' => 'hamster:read']);
    }
}
