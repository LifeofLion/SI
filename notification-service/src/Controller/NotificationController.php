<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    /**
     * @Route("/notification/send", name="send_notification", methods={"POST"})
     */
    public function sendNotification(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $notification = new Notification();
        $notification->setEmailRecipient($data['email_recipient']);
        $notification->setMessage($data['message']);
        $notification->setSujet($data['sujet']);

        $entityManager->persist($notification);
        $entityManager->flush();

        // Logique d'envoi d'email (à implémenter)

        return new JsonResponse(['status' => 'Notification sent!'], JsonResponse::HTTP_CREATED);
    }
}
