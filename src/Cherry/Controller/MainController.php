<?php

namespace Cherry\Controller;

use Cherry\Application;

class MainController
{
    public function aboutAction(Application $app)
    {
        return $app['twig']->render('Main/about.html.twig');
    }

    public function homepageAction(Application $app)
    {
        return $app['twig']->render('Main/homepage.html.twig');
    }
}
