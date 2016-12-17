<?php

namespace Cherry\Controller;

use Cherry\Application;

class AdminController
{
    public function dashboardAction(Application $app)
    {
        return $app['twig']->render('Admin/dashboard.html.twig');
    }
}
