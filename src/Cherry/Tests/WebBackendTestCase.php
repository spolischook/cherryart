<?php

namespace Cherry\Tests;

use Cherry\BackendApplication;

abstract class WebBackendTestCase extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    public function createClient(array $server = [])
    {
        return parent::createClient(
            array_merge(
                $server,
                ['HTTP_HOST' => WEB_SERVER_HOST.':'.WEB_SERVER_PORT_BACKEND]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createApplication()
    {
        $app = new BackendApplication('test');
        $app['debug'] = true;
        $app['session.test'] = true;

        return $app;
    }
}
