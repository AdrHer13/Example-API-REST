<?php

namespace App\Controller;

// use Symfony\Bridge\Doctrine\ManagerRegistry;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user_index')]
    public function index(): Response
    {
        return $this->render('user_management/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/all', name: 'user_get_all_users', methods: ['get'])]
    public function getAllUsers(EntityManager $em): JsonResponse
    {
        $users = $em
            ->getRepository(User::class)
            ->findAll();
        $data = [];

        foreach ($users as $user) {
            if (!$user->getDeletedAt()) {
                $data[] = [
                    'id' => $user->getId(),
                    'createdAt' => $user->getCreatedAt(),
                    'updatedAt' => $user->getUpdatedAt(),
                    'deletedAt' => $user->getDeletedAt(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                ];
            }
        }

        return $this->json($data);
    }

    #[Route('/user/create', name: 'user_create', methods: ['post'])]
    public function create(EntityManager $em, Request $request): JsonResponse
    {
        $user = new User();
        $user->setName($request->request->get('name'));
        $user->setEmail($request->request->get('email'));

        $em->persist($user);
        $em->flush();

        $data =  [
            'id' => $user->getId(),
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
            'deletedAt' => $user->getDeletedAt(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];

        return $this->json($data);
    }

    #[Route('/user/{id}', name: 'user_show', methods: ['get'])]
    public function show(EntityManager $em, int $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json('No user found for id ' . $id, 404);
        }

        $data =  [
            'id' => $user->getId(),
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
            'deletedAt' => $user->getDeletedAt(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];

        return $this->json($data);
    }

    #[Route('/user/{id}', name: 'user_update', methods: ['put'])]
    public function update(EntityManager $em, Request $request, int $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json('No user found for id' . $id, 404);
        }

        $user->setName($request->request->get('name'));
        $user->setEmail($request->request->get('email'));
        $em->flush();

        $data =  [
            'id' => $user->getId(),
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
            'deletedAt' => $user->getDeletedAt(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];

        return $this->json($data);
    }

    #[Route('/user/{id}', name: 'user_delete', methods: ['delete'])]
    public function delete(EntityManager $em, int $id): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json('No user found for id' . $id, 404);
        }

        $user->setDeletedAt(new \DateTime("now"));
        $em->flush();

        return $this->json('Deleted a user successfully with id ' . $id);
    }
}
