<?php

namespace Cherry\Tests;

use Cherry\FrontendApplication;

abstract class WebFrontendTestCase extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    public function createClient(array $server = [])
    {
        return parent::createClient(
            array_merge(
                $server,
                ['HTTP_HOST' => WEB_SERVER_HOST.':'.WEB_SERVER_PORT_FRONTEND]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createApplication()
    {
        $app = new FrontendApplication('test');
        $app['debug'] = true;

        return $app;
    }
}
