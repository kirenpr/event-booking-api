<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\Traits\JsonTestTrait;

class AttendeeControllerTest extends WebTestCase
{

    use JsonTestTrait;

    public function testRegisterAttendeeWithValidData(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Test User',
            'email' => 'testuser@example.com'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains($client->getResponse(), ['message' => 'Attendee registered successfully']);
    }

    public function testRegisterAttendeeWithMissingFields(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => '',
            'email' => ''
        ]));

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains($client->getResponse(), ["name" => "Name is required.", "email" => "Email Address is required."]);
    }

    public function testRegisterAttendeeWithInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Fake Name',
            'email' => 'invalid-email'
        ]));

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains($client->getResponse(), ["email" => 'The email "invalid-email" is not a valid email.']);
    }
}
