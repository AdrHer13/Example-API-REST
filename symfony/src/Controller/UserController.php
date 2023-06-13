<?php

namespace App\Controller;

// use Symfony\Bridge\Doctrine\ManagerRegistry;

use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class UserController extends AbstractController
{
    #[Route('/', name: 'user_landing')]
    public function landing(Environment $environment): Response
    {
        return new Response($environment->render('base.html.twig', []));
    }

    #[Route('/user', name: 'user_index')]
    public function index(EntityManagerInterface $em, Environment $environment): Response
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

        return new Response($environment->render('user/index.html.twig', [
            'users' => $data,
        ]));
    }

    /**
     * Uses GET for rendering the form. When POST is used, it flushes the data
     */
    #[Route('/user/new', name: 'user_create', methods: ['get', 'post'])]
    public function create(EntityManagerInterface $em, Request $request, Environment $environment): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_index');
        }

        return new Response($environment->render('user/show.html.twig', [
            'user_form' => $form->createView()
        ]));
    }

    #[Route('/user/show/{id}', name: 'user_show', methods: ['get'])]
    public function show(EntityManagerInterface $em, int $id, Environment $environment): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('user_create');
        }

        $form = $this->createForm(UserFormType::class, $user, [
            'action' => $this->generateUrl('user_update', ['id' => $id]),
            'method' => "PUT",
        ]);

        return new Response($environment->render('user/show.html.twig', [
            'user_form' => $form->createView(),
            'user' => $user,
        ]));
    }

    #[Route('/user/update/{id}', name: 'user_update', methods: ['put'])]
    public function update(EntityManagerInterface $em, Request $request, int $id): mixed
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json('No user found for id' . $id, 404);
        }

        $user->setName($request->get('user_form')['name']);
        $user->setEmail($request->get('user_form')['email']);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('user_index');
    }

    #[Route('/user/delete', name: 'user_delete', methods: ['delete'])]
    public function delete(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $id = $request->get('user_id');

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $user->setDeletedAt(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));
        try {
            $em->persist($user);
            $em->flush();
        } catch (Exception $e) {
            $errorMessage = 'An error occurred while deleting user with id ' . $id . ': ' . $e->getMessage();
            return new JsonResponse($errorMessage);
        }

        $data = [
            'redirect_url' => $this->generateUrl('user_index'),
            'message' => 'Deleted user successfully with id ' . $id,
        ];
        return new JsonResponse($data);
    }
}
