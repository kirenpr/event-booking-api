<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Event;
use App\Entity\Attendee;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\Traits\JsonTestTrait;
use DateTime;

class BookingControllerTest extends WebTestCase
{

    use JsonTestTrait;

    private EntityManagerInterface $entityManager;
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createClientWithJsonHeaders();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    private function createClientWithJsonHeaders(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $client = static::createClient();
        $client->setServerParameters([
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);
        return $client;
    }

    public function testBookEventSuccessfully(): void
    {
        // Create event and attendee
        $this->client->request('POST', '/api/events', [], [], [], json_encode([
            'title' => 'Test Event',
            'description' => 'A global tech meetup.',
            'date' => '25/05/2025',
            'country' => 'India',
            'capacity' => 100
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $eventId = $data['id'];

        $this->client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Booking User',
            'email' => 'bookinguser@example.com'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $attendeeId = $data['id'];

        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $eventId,
            'attendee_id' => $attendeeId,
            'booked_at' => date('d/m/Y')
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains($this->client->getResponse(), ['message' => 'Booking successful']);
    }

    public function testDuplicateBooking(): void
    {

        // Assume attendee and event already exist from the previous test or fixture
        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['title' => 'Test Event']);
        $attendee = $this->entityManager->getRepository(Attendee::class)->findOneBy(['email' => 'bookinguser@example.com']);

        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $event->getId(),
            'attendee_id' => $attendee->getId(),
            'booked_at' => date('d/m/Y')
        ]));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains($this->client->getResponse(), ['error' => 'Attendee already booked for this event']);
    }

    public function testOverbooking(): void
    {

        // Create a new event with capacity = 0

        $this->client->request('POST', '/api/events', [], [], [], json_encode([
            'title' => 'Full Event',
            'description' => 'A global tech meetup.',
            'date' => '25/05/2025',
            'country' => 'India',
            'capacity' => 2
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $eventId = $data['id'];

        $this->client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Another User',
            'email' => 'anotheruser@example.com'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $attendeeId = $data['id'];

        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $eventId,
            'attendee_id' => $attendeeId,
            'booked_at' => date('d/m/Y')
        ]));

        $attendee = $this->entityManager->getRepository(Attendee::class)->findOneBy(['email' => 'bookinguser@example.com']);

        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $eventId,
            'attendee_id' => $attendee->getId(),
            'booked_at' => date('d/m/Y')
        ]));

        $this->client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Yet Another User',
            'email' => 'yetanotheruser@example.com'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $attendeeId = $data['id'];

        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $eventId,
            'attendee_id' => $attendeeId,
            'booked_at' => date('d/m/Y')
        ]));

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains($this->client->getResponse(), ['error' => 'Event is fully booked']);
    }
}
