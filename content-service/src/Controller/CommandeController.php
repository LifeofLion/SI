<?php
// src/Controller/CommandeController.php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    /**
     * @Route("/orders", methods={"POST"})
     */
    public function createOrder(Request $request, EntityManagerInterface $em)
    {
        $data = json_decode($request->getContent(), true);

        $order = new Commande();
        $order->setProductId($data['product_id']);
        $order->setCustomerEmail($data['customer_email']);
        $order->setQuantity($data['quantity']);
        $order->setTotalPrice($data['total_price']);

        $em->persist($order);
        $em->flush();

        return new JsonResponse(['status' => 'Order created'], JsonResponse::HTTP_CREATED);
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
