<?php

namespace App\Controller;

use App\Entity\Attendee;
use App\Repository\AttendeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/attendees')]
class AttendeeController extends AbstractController
{
    #[Route('', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $attendee = new Attendee();
        $attendee->setName($data['name'] ?? '');
        $attendee->setEmail($data['email'] ?? '');

        $errors = $validator->validate($attendee);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json($errorMessages, 422);
        }

        $em->persist($attendee);
        $em->flush();

        return $this->json(['message' => 'Attendee registered successfully', 'id' => $attendee->getId()], 201);
    }

    #[Route('', methods: ['GET'])]
    public function list(AttendeeRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }
}
