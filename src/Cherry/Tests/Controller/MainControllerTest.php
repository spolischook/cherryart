<?php

namespace Cherry\Tests\Controller;

use Cherry\Tests\WebFrontendTestCase;

class MainControllerFrontendTest extends WebFrontendTestCase
{
    public function testRedirectToDefaultLocale()
    {
        $client = $this->createClient();
        $client->request('GET', '/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/en', $client->getResponse()->headers->get('location'));
    }

    public function testHomeActionAfterRedirect()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Homepage")'));
    }

    public function testAboutMeAction()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/en/Tetiana-Cherevan');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Tetiana Cherevan")'));

        $crawler = $client->request('GET', '/uk/Tetiana-Cherevan');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Тетяна Черевань")'));
    }
}
