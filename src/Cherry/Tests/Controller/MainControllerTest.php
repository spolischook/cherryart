<?php

namespace Cherry\Tests\Controller;

use Cherry\Tests\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testHomeAction()
    {
        $client = $this->createClient(['HTTP_HOST' => 'cherryart.local', 'HTTP_ACCEPT_LANGUAGE' => 'ru,en-US;q=0.8,en;q=0.6,uk;q=0.4']);
        $client->request('GET', '/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/en', $client->getResponse()->headers->get('location'));
    }

    public function testHomeActionAfterRedirect()
    {
        $client = $this->createClient(['HTTP_HOST' => 'cherryart.local', 'HTTP_ACCEPT_LANGUAGE' => 'ru,en-US;q=0.8,en;q=0.6,uk;q=0.4']);
        $client->followRedirects();
        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Homepage")'));
    }
}
