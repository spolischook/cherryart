<?php

namespace Cherry\Controller;

use Cherry\Application;
use Symfony\Component\HttpFoundation\Request;

class ArtWorksController
{
    public function viewAction($slug, Request $request, Application $app)
    {
        return $app['twig']->render('ArtWorks/view.html.twig', ['slug' => $slug]);
    }

    public function listAction(Request $request, Application $app)
    {
        return $app['twig']->render('ArtWorks/list.html.twig', [
            'works' => $app['db']->fetchAll('SELECT * FROM `art_works` ORDER BY `date_unix` DESC LIMIT 50'),
        ]);
    }
}
