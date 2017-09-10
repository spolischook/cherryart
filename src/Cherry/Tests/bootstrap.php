<?php

//startWebServer(WEB_SERVER_HOST, WEB_SERVER_PORT_BACKEND, WEB_SERVER_DOCROOT_BACKEND);
//startWebServer(WEB_SERVER_HOST, WEB_SERVER_PORT_FRONTEND, WEB_SERVER_DOCROOT_FRONTEND);

function startWebServer($host, $port, $docRoot) {
    // Command that starts the built-in web server
    $webserverCommand = sprintf(
        'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
        $host,
        $port,
        $docRoot
    );

    // Execute the command and store the process ID
    $output = [];
    exec($webserverCommand, $output);
    $commandPid = (int) $output[0];

    echo sprintf(
            '%s - Web server started on %s:%d with PID %d',
            date('r'),
            $host,
            $port,
            $commandPid
        ) . PHP_EOL;

    // Kill the web server when the process ends
    register_shutdown_function(function() use ($commandPid) {
        echo sprintf('%s - Killing process with ID %d', date('r'), $commandPid) . PHP_EOL;
        exec('kill ' . $commandPid);
    });
}
