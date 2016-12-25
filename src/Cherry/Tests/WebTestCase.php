<?php

namespace Cherry\Tests;

use Cherry\Application;
use Silex\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
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
