<?php

namespace Cherry\Tests;

use Cherry\Application;
use Doctrine\DBAL\Connection;
use Silex\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    private $schema = Application::APPLICATION_PATH.'/../../schema.sql';

    protected function setUp()
    {
        parent::setUp();
        /** @var Connection $db */
        $db = $this->app::$db;
        $db->getSchemaManager()->createDatabase($this->app['db_path']);
        $db->exec(file_get_contents($this->schema));
    }

    protected function tearDown()
    {
        parent::tearDown();
        /** @var Connection $db */
        $db = $this->app::$db;
        $db->getSchemaManager()->dropDatabase($this->app['db_path']);
    }
}
