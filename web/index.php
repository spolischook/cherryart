<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Cherry\FrontendApplication();
$app['debug'] = true;

$app->run();
