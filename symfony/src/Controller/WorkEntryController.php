<?php

namespace App\Controller;

use App\Entity\WorkEntry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorkEntryController extends AbstractController
{
    #[Route('/work_entry', name: 'work_index')]
    public function index(): Response
    {
        return $this->render('work_entry/index.html.twig', [
            'controller_name' => 'WorkEntryController',
        ]);
    }

    #[Route('/work_entry/all', name: 'work_get_all_work_entries', methods: ['get'])]
    public function getAllWorkEntries(EntityManager $em): JsonResponse
    {
        $workEntries = $em
            ->getRepository(WorkEntry::class)
            ->findAll();
        $data = [];

        foreach ($workEntries as $workEntry) {
            if (!$workEntry->getDeletedAt()) {
                $data[] = [
                    'id' => $workEntry->getId(),
                    'userId' => $workEntry->getUserId(),
                    'createdAt' => $workEntry->getCreatedAt(),
                    'updatedAt' => $workEntry->getUpdatedAt(),
                    'deletedAt' => $workEntry->getDeletedAt(),
                    'startDate' => $workEntry->getStartDate(),
                    'endDate' => $workEntry->getEndDate(),
                ];
            }
        }

        return $this->json($data);
    }

    #[Route('/work_entry/create', name: 'work_create', methods: ['post'])]
    public function create(EntityManager $em, Request $request): JsonResponse
    {
        $workEntry = new WorkEntry();
        $workEntry->setUserId($request->request->get('user_id'));
        # El resto es automático?

        $em->persist($workEntry);
        $em->flush();

        $data =  [
            'id' => $workEntry->getId(),
            'userId' => $workEntry->getUserId(),
            'createdAt' => $workEntry->getCreatedAt(),
            'updatedAt' => $workEntry->getUpdatedAt(),
            'deletedAt' => $workEntry->getDeletedAt(),
            'startDate' => $workEntry->getStartDate(),
            'endDate' => $workEntry->getEndDate(),
        ];

        return $this->json($data);
    }

    #[Route('/work_entry/{id}', name: 'work_show', methods: ['get'])]
    public function show(EntityManager $em, int $id): JsonResponse
    {
        $workEntry = $em->getRepository(WorkEntry::class)->find($id);

        if (!$workEntry) {
            return $this->json('No work entry found for id ' . $id, 404);
        }

        $data =  [
            'id' => $workEntry->getId(),
            'userId' => $workEntry->getUserId(),
            'createdAt' => $workEntry->getCreatedAt(),
            'updatedAt' => $workEntry->getUpdatedAt(),
            'deletedAt' => $workEntry->getDeletedAt(),
            'startDate' => $workEntry->getStartDate(),
            'endDate' => $workEntry->getEndDate(),
        ];

        return $this->json($data);
    }

    #[Route('/work_entry/{id}', name: 'work_update', methods: ['put'])]
    public function update(EntityManager $em, Request $request, int $id): JsonResponse
    {
        $workEntry = $em->getRepository(WorkEntry::class)->find($id);

        if (!$workEntry) {
            return $this->json('No work entry found for id' . $id, 404);
        }

        $workEntry->setUserId($request->request->get('user_id'));
        # ???
        $em->flush();

        $data =  [
            'id' => $workEntry->getId(),
            'userId' => $workEntry->getUserId(),
            'createdAt' => $workEntry->getCreatedAt(),
            'updatedAt' => $workEntry->getUpdatedAt(),
            'deletedAt' => $workEntry->getDeletedAt(),
            'startDate' => $workEntry->getStartDate(),
            'endDate' => $workEntry->getEndDate(),
        ];

        return $this->json($data);
    }

    #[Route('/work_entry/{id}', name: 'work_delete', methods: ['delete'])]
    public function delete(EntityManager $em, int $id): JsonResponse
    {
        $workEntry = $em->getRepository(WorkEntry::class)->find($id);

        if (!$workEntry) {
            return $this->json('No work entry found for id' . $id, 404);
        }

        $workEntry->setDeletedAt(new \DateTime("now"));
        $em->flush();

        return $this->json('Deleted a work entry successfully with id ' . $id);
    }
}
