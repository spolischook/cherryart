<?php

namespace Cherry\Tests\Controller;

use Cherry\Tests\WebBackendTestCase;

class AdminArtWorksControllerTest extends WebBackendTestCase
{
    public function testListActionUnauthorized()
    {
        $client = $this->createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/art-works');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h2:contains("Please sign in")'));
    }
}
