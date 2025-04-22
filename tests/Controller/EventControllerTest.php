<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Traits\JsonTestTrait;

class EventControllerTest extends WebTestCase
{

    use JsonTestTrait;

    private function createClientWithJsonHeaders(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $client = static::createClient();
        $client->setServerParameters([
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);
        return $client;
    }

    public function testCreateEventSuccessfully(): void
    {
        $client = $this->createClientWithJsonHeaders();

        $client->request('POST', '/api/events', [], [], [], json_encode([
            'title' => 'Tech Summit 2025',
            'description' => 'A global tech meetup.',
            'date' => '25/05/2025',
            'country' => 'India',
            'capacity' => 100
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonContains($client->getResponse(), ['title' => 'Tech Summit 2025']);
    }

    public function testCreateEventWithInvalidDate(): void
    {
        $client = $this->createClientWithJsonHeaders();

        $client->request('POST', '/api/events', [], [], [], json_encode([
            'title' => 'Invalid Event',
            'description' => 'Wrong date',
            'date' => '31-02-2025',
            'country' => 'India',
            'capacity' => 50
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonContains($client->getResponse(), ['date_format_error' => 'Invalid date. Use d/m/Y format.']);
    }

    public function testCreateEventWithMissingFields(): void
    {
        $client = $this->createClientWithJsonHeaders();

        $client->request('POST', '/api/events', [], [], [], json_encode(['date' => '30/02/2020']));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonContains($client->getResponse(), [
            'title' => "Event title is required.",
            'date' => "Event date must be in the future.",
            'capacity' => "Event capacity must be greater than 0.",
            'country' => "Event location is required."
        ]);
    }

    public function testGetEventList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/events');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testGetNonExistingEvent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/events/999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJsonContains($client->getResponse(), ['error' => 'Event not found.']);
    }

    public function testUpdateEventWithInvalidCapacity(): void
    {
        $client = $this->createClientWithJsonHeaders();

        // First create an event
        $client->request('POST', '/api/events', [], [], [], json_encode([
            'title' => 'Temp Event',
            'description' => 'Just a temp',
            'date' => '01/12/2025',
            'country' => 'India',
            'capacity' => 30
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);
        $id = $data['id'];

        // Try updating with bad capacity
        $client->request('PUT', "/api/events/{$id}", [], [], [], json_encode([
            'capacity' => -5
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonContains($client->getResponse(), [
            'capacity' => 'Event capacity must be greater than 0.'
        ]);
    }

    public function testDeleteEvent(): void
    {
        $client = $this->createClientWithJsonHeaders();

        // Create
        $client->request('POST', '/api/events', [], [], [], json_encode([
            'title' => 'Delete Me',
            'description' => 'To be deleted',
            'date' => '20/11/2025',
            'country' => 'UK',
            'capacity' => 10
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);
        $id = $data['id'];

        // Delete
        $client->request('DELETE', "/api/events/{$id}");
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
