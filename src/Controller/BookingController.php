<?php
// src/Controller/BookingController.php
namespace App\Controller;

use App\Entity\Booking;
use App\Repository\AttendeeRepository;
use App\Repository\EventRepository;
use App\Repository\BookingRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/bookings')]
class BookingController extends AbstractController
{
    #[Route('', methods: ['POST'])]
    public function book(
        Request $request,
        EventRepository $eventRepo,
        AttendeeRepository $attendeeRepo,
        BookingRepository $bookingRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $event = $eventRepo->find($data['event_id'] ?? 0);
        $attendee = $attendeeRepo->find($data['attendee_id'] ?? 0);
        $bookedAt = $data['booked_at'] ?? date('d/m/Y');
        $bookedAt = DateTimeImmutable::createFromFormat('d/m/Y', $bookedAt);

        if (!$event || !$attendee) {
            return $this->json(['error' => 'Invalid event or attendee'], 400);
        }

        // Prevent duplicate booking
        if ($bookingRepo->findOneBy(['event' => $event, 'attendee' => $attendee])) {
            return $this->json(['error' => 'Attendee already booked for this event'], 400);
        }

        // Check capacity
        $existingBookings = $bookingRepo->count(['event' => $event]);
        if ($existingBookings >= $event->getCapacity()) {
            return $this->json(['error' => 'Event is fully booked'], 400);
        }

        $booking = new Booking();
        $booking->setEvent($event);
        $booking->setAttendee($attendee);
        $booking->setBookedAt($bookedAt);
        $em->persist($booking);
        $em->flush();

        return $this->json(['message' => 'Booking successful', 'id' => $booking->getId()], 201);
    }
}
