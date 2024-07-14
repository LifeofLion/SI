<?php

namespace App\Controller;

use App\Entity\Facture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
{
    #[Route('/facture', name: 'app_facture')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/FactureController.php',
        ]);
    }

    #[Route('/facture/create', name: 'create_facture', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $facture = new Facture();
        $facture->setAmount($data['amount']);
        $facture->setDueDate(new \DateTime($data['due_date']));
        $facture->setCustomerEmail($data['customer_email']);

        $entityManager->persist($facture);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Facture created!'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/facture/read', name: 'read_facture', methods: ['POST'])]
    public function read(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $facture = $entityManager->getRepository(Facture::class)->find($data['id']);

        if (!$facture) {
            return new JsonResponse(['status' => 'Facture not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $facture->getId(),
            'amount' => $facture->getAmount(),
            'due_date' => $facture->getDueDate()->format('Y-m-d'),
            'customer_email' => $facture->getCustomerEmail(),
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/facture/update', name: 'update_facture', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $facture = $entityManager->getRepository(Facture::class)->find($data['id']);

        if (!$facture) {
            return new JsonResponse(['status' => 'Facture not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $facture->setAmount($data['amount']);
        $facture->setDueDate(new \DateTime($data['due_date']));
        $facture->setCustomerEmail($data['customer_email']);

        $entityManager->flush();

        return new JsonResponse(['status' => 'Facture updated!'], JsonResponse::HTTP_OK);
    }

    #[Route('/facture/delete', name: 'delete_facture', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $facture = $entityManager->getRepository(Facture::class)->find($data['id']);

        if (!$facture) {
            return new JsonResponse(['status' => 'Facture not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($facture);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Facture deleted!'], JsonResponse::HTTP_OK);
    }
}
