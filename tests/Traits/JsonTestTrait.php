<?php

namespace App\Tests\Traits;

use Symfony\Component\HttpFoundation\Response;

trait JsonTestTrait
{
    public function assertJsonContains(Response $response, array $expectedSubset): void
    {
        $json = $response->getContent();
        $this->assertJson($json);

        $data = json_decode($json, true);
        $this->assertIsArray($data);

        foreach ($expectedSubset as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertEquals($value, $data[$key]);
        }
    }
}
