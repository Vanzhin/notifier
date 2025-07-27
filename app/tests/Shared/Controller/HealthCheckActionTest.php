<?php

namespace Shared\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class HealthCheckActionTest extends WebTestCase
{
    public function testRequestRespondedSuccessfulResult(): void
    {
        // arrange
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/health-check');

        // act
        $jsonResult = json_decode($client->getResponse()->getContent(), true);

        // assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($jsonResult['data']['status'], 'ok');
    }
}
