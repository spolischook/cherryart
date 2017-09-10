<?php

namespace Cherry\Tests;

use Cherry\FrontendApplication;
use Silex\WebTestCase as BaseWebTestCase;

abstract class WebFrontendTestCase extends BaseWebTestCase
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
        $app = new FrontendApplication();
        $app['debug'] = true;

        return $app;
    }
}
