<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    $app = new Cherry\BackendApplication();
    $app['debug'] = true;

    $app->run();
} catch (\Exception $e) {
    $app['monolog']->error((string) $e);
} catch (\Throwable $e) {
    $app['monolog']->error((string) $e);
}
