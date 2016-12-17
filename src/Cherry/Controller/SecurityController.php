<?php

namespace Cherry\Controller;

use Cherry\Application;
use Symfony\Component\HttpFoundation\Request;

class SecurityController
{
    public function loginAction(Request $request, Application $app)
    {
        return $app['twig']->render('Security/login.html.twig', [
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ]);
    }
}
