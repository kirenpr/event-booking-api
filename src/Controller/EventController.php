<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\EventValidatorService;

#[Route('/api/events', name: 'api_events_')]
final class EventController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventRepository $eventRepo,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $events = $this->eventRepo->findAll();
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EventValidatorService $eventValidator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $event = new Event();

        $event->setTitle($data['title'] ?? '');
        $event->setDescription($data['description'] ?? '');
        $event->setCountry($data['country'] ?? '');
        $event->setCapacity((int) ($data['capacity'] ?? 0));

        $date = $eventValidator->isValidDateTime($data['date']);

        if (!$date) {
            return $this->json(['date_format_error' => 'Invalid date. Use d/m/Y format.'], 422);
        }
        $event->setDate($date);

        $errors = $eventValidator->validate($event);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->persist($event);
        $this->em->flush();

        return $this->json($event, Response::HTTP_CREATED, [], ['groups' => 'event:read']);
    }

    #[Route('/{id}', name: 'view', methods: ['GET'])]
    public function view(int $id): JsonResponse
    {
        $event = $this->eventRepo->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found.'], 404);
        }

        return $this->json($event, 200, [], ['groups' => 'event:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id, EventValidatorService $eventValidator): JsonResponse
    {
        $event = $this->eventRepo->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $event->setTitle($data['title'] ?? $event->getTitle());
        $event->setDescription($data['description'] ?? $event->getDescription());
        $event->setCountry($data['country'] ?? $event->getCountry());
        $event->setCapacity((int) ($data['capacity'] ?? $event->getCapacity()));

        $date = $eventValidator->isValidDateTime($data['date'] ?? $event->getDate()->format('d/m/Y'));

        if (!$date) {
            return $this->json(['date_format_error' => 'Invalid date. Use d/m/Y format.'], 422);
        }
        $event->setDate($date);

        $errors = $eventValidator->validate($event);

        if (count($errors) > 0) {
            return $this->json($errors, 422);
        }

        $this->em->flush();

        return $this->json($event, 200, [], ['groups' => 'event:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $event = $this->eventRepo->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found.'], 404);
        }

        $this->em->remove($event);
        $this->em->flush();

        return $this->json(['message' => 'Event deleted.'], 204);
    }
}
