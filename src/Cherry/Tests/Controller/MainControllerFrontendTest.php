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
        $this->assertContains('art works', strtolower($crawler->filter('title')->text()));
    }

    public function testCvAction()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/en/cv');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h3:contains("Group")'));
        $this->assertCount(1, $crawler->filter('h3:contains("Solo")'));

        $crawler = $client->request('GET', '/uk/cv');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h3:contains("Групові виставки")'));
        $this->assertCount(1, $crawler->filter('h3:contains("Персональні виставки")'));
    }
}
