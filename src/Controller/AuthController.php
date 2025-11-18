<?php

namespace App\Controller;

use App\DTO\CreateUserDTO;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
final class AuthController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        
    ) {}

    #[Route('/register', name: 'user_register', methods: ['POST'], format: 'json')]
    public function register(Request $request): JsonResponse
    {

        $dto = $this->serializer->deserialize($request->getContent(), CreateUserDTO::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userService->createUser($dto);

        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => 'user:read']);
    }

    #[Route('/user', name: 'show_me', methods: ['GET'], format: 'json')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => 'user:read']
        );
    }

    #[Route('/delete/{id}', name: 'user_delete', methods: ['DELETE'], format: 'json')]
    #[IsGranted('ROLE_ADMIN', statusCode: 423)]
    public function deleteUser(int $id): JsonResponse
    {

        $this->userService->deleteUser($id);
        return $this->json([
            'Success',
            Response::HTTP_OK
        ]);
    }
}
