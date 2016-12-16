<?php

require_once __DIR__.'/../vendor/autoload.php';

\Symfony\Component\Debug\ErrorHandler::register();
$app = new Cherry\Application();
$app['debug'] = true;

$app->run();
