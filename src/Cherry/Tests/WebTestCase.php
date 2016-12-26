<?php

namespace Cherry\Tests;

use Cherry\Application;
use Silex\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    /**
     * {@inheritdoc}
     */
    public function createClient(array $server = [])
    {
        if (isset($server['HTTP_HOST'])) {
            return parent::createClient($server);
        }

        if (!isset($GLOBALS['http_host'])) {
            return parent::createClient($server);
        }

        return parent::createClient(
            array_merge(
                $server,
                ['HTTP_HOST' => $GLOBALS['http_host']]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;

        return $app;
    }
}
