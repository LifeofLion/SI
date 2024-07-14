<?php
// src/Controller/CommandeController.php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommandeController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @Route("/orders", methods={"POST"})
     */
    public function createOrder(Request $request, EntityManagerInterface $em)
    {
        $data = json_decode($request->getContent(), true);

        // Create the order
        $order = new Commande();
        $order->setProductId($data['product_id']);
        $order->setCustomerEmail($data['customer_email']);
        $order->setQuantity($data['quantity']);
        $order->setTotalPrice($data['total_price']);

        $em->persist($order);
        $em->flush();

        // Send request to Billing Service to create an invoice
        try {
            $response = $this->httpClient->request('POST', 'http://127.0.0.1/facture/create', [
                'json' => [
                    'amount' => $data['total_price'],
                    'due_date' => (new \DateTime('+30 days'))->format('Y-m-d'),
                    'customer_email' => $data['customer_email']
                ]
            ]);

            if ($response->getStatusCode() !== 201) {
                return new JsonResponse(['status' => 'Order created but failed to create invoice'], JsonResponse::HTTP_CREATED);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'Order created but failed to create invoice', 'error' => $e->getMessage()], JsonResponse::HTTP_CREATED);
        }

        return new JsonResponse(['status' => 'Order created and invoice created'], JsonResponse::HTTP_CREATED);
    }

    /**
     * @Route("/orders/{id}", methods={"GET"})
     */
    public function getOrder($id, EntityManagerInterface $em)
    {
        $order = $em->getRepository(Commande::class)->find($id);

        if (!$order) {
            return new JsonResponse(['status' => 'Order not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($order);
    }

    /**
     * @Route("/orders", methods={"GET"})
     */
    public function getOrders(EntityManagerInterface $em)
    {
        $orders = $em->getRepository(Commande::class)->findAll();

        return new JsonResponse($orders);
    }

    /**
     * @Route("/orders/{id}", methods={"PUT"})
     */
    public function updateOrder($id, Request $request, EntityManagerInterface $em)
    {
        $data = json_decode($request->getContent(), true);

        $order = $em->getRepository(Commande::class)->find($id);

        if (!$order) {
            return new JsonResponse(['status' => 'Order not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $order->setProductId($data['product_id']);
        $order->setCustomerEmail($data['customer_email']);
        $order->setQuantity($data['quantity']);
        $order->setTotalPrice($data['total_price']);

        $em->flush();

        return new JsonResponse(['status' => 'Order updated'], JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/orders/{id}", methods={"DELETE"})
     */
    public function deleteOrder($id, EntityManagerInterface $em)
    {
        $order = $em->getRepository(Commande::class)->find($id);

        if (!$order) {
            return new JsonResponse(['status' => 'Order not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $em->remove($order);
        $em->flush();

        return new JsonResponse(['status' => 'Order deleted'], JsonResponse::HTTP_OK);
    }
}
