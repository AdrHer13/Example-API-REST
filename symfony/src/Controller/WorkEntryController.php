<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\WorkEntry;
use App\Form\WorkEntryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

use function PHPUnit\Framework\lessThan;

class WorkEntryController extends AbstractController
{
    #[Route('/work_entry', name: 'work_index')]
    public function index(EntityManagerInterface $em, Environment $environment): Response
    {
        $queryBuilder = $em->createQueryBuilder()
            ->select('we')
            ->from(WorkEntry::class, 'we')
            ->join('we.userId', 'u')
            ->where('we.deletedAt IS NULL')
            ->andWhere('u.deletedAt IS NULL')
            ->orderBy('we.id', 'ASC');
        $workEntries = $queryBuilder->getQuery()->getResult();

        $data = [];

        foreach ($workEntries as $workEntry) {
            if (!$workEntry->getDeletedAt()) {
                $data[] = [
                    'id' => $workEntry->getId(),
                    'user' => $workEntry->getUserId(),
                    'createdAt' => $workEntry->getCreatedAt(),
                    'updatedAt' => $workEntry->getUpdatedAt(),
                    'deletedAt' => $workEntry->getDeletedAt(),
                    'startDate' => $workEntry->getStartDate(),
                    'endDate' => $workEntry->getEndDate(),
                ];
            }
        }
        return new Response($environment->render('work_entry/index.html.twig', [
            'work_entries' => $data,
        ]));
    }

    /**
     * Uses GET for rendering the form. When POST is used, it flushes the data
     */
    #[Route('/work_entry/new', name: 'work_create', methods: ['get', 'post'])]
    public function create(EntityManagerInterface $em, Request $request, Environment $environment): Response
    {
        $workEntry = new WorkEntry();
        $form = $this->createForm(WorkEntryFormType::class, $workEntry);

        $form->handleRequest($request);
        
        if ($form->getData()->getEndDate() < $form->getData()->getStartDate() and !is_null($form->getData()->getEndDate())) {
            throw new Exception('EndDate cannot be less than StartDate');
        }

        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($workEntry);
            $em->flush();

            return $this->redirectToRoute('work_index');
        }

        return new Response($environment->render('work_entry/show.html.twig', [
            'work_entry_form' => $form->createView()
        ]));
    }

    #[Route('/work_entry/show/{id}', name: 'work_show', methods: ['get'])]
    public function show(EntityManagerInterface $em, int $id, Environment $environment): Response
    {
        $workEntry = $em->getRepository(WorkEntry::class)->find($id);

        if (!$workEntry) {
            return $this->redirectToRoute('work_create');
        }

        $form = $this->createForm(WorkEntryFormType::class, $workEntry, [
            'action' => $this->generateUrl('work_update', ['id' => $id]),
            'method' => "PUT",
        ]);

        return new Response($environment->render('work_entry/show.html.twig', [
            'work_entry_form' => $form->createView(),
            'work_entry' => $workEntry,
        ]));
    }

    #[Route('/work_entry/update/{id}', name: 'work_update', methods: ['put'])]
    public function update(EntityManagerInterface $em, Request $request, int $id): mixed
    {
        $workEntry = $em->getRepository(WorkEntry::class)->find($id);

        if (!$workEntry) {
            return $this->json('No work entry found for id' . $id, 404);
        }

        $startDateRaw = $request->get('work_entry_form')['startDate'];
        $startDate = date_create_from_format('d-m-Y h:i', str_pad($startDateRaw['date']['day'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($startDateRaw['date']['month'], 2, "0", STR_PAD_LEFT) . '-' . $startDateRaw['date']['year'] . ' ' . str_pad($startDateRaw['time']['hour'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($startDateRaw['time']['minute'], 2, "0", STR_PAD_LEFT));

        if ($request->get('work_entry_form')['endDate']){
            $endDateRaw = $request->get('work_entry_form')['endDate'];
            $endDate = date_create_from_format('d-m-Y h:i', str_pad($endDateRaw['date']['day'], 2, "0", STR_PAD_LEFT) . '-' . str_pad($endDateRaw['date']['month'], 2, "0", STR_PAD_LEFT) . '-' . $endDateRaw['date']['year'] . ' ' . str_pad($endDateRaw['time']['hour'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($endDateRaw['time']['minute'], 2, "0", STR_PAD_LEFT), new \DateTimeZone('Europe/Madrid'));
    
            if ($endDate < $startDate and !is_null($endDate)) {
                throw new Exception('EndDate cannot be less than StartDate');
            }

            $workEntry->setEndDate($endDate);
        }

        $user = $em->getRepository(User::class)->find($request->get('work_entry_form')['userId']);
        $workEntry->setUserId($user);
        $workEntry->setStartDate($startDate);
        
        $em->persist($workEntry);
        $em->flush();

        return $this->redirectToRoute('work_index');
    }

    #[Route('/work_entry/delete', name: 'work_delete', methods: ['delete'])]
    public function delete(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $id = $request->get('work_entry_id');

        $workEntry = $em->getRepository(WorkEntry::class)->find($id);

        if (!$workEntry) {
            throw $this->createNotFoundException('Work entry not found');
        }

        $workEntry->setDeletedAt(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));

        try {
            $em->persist($workEntry);
            $em->flush();
        } catch (Exception $e) {
            $errorMessage = 'An error occurred while deleting work entry with id ' . $id . ': ' . $e->getMessage();
            return new JsonResponse($errorMessage);
        }

        $data = [
            'redirect_url' => $this->generateUrl('work_index'),
            'message' => 'Deleted work entry successfully with id ' . $id,
        ];
        return new JsonResponse($data);
    }
}
