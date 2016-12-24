<?php

namespace Cherry\Controller;

use Cherry\Application;
use Cherry\Form\ArtWorkType;
use Cherry\ImageHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminController
{
    public function dashboardAction(Application $app)
    {
        return $app['twig']->render('Admin/dashboard.html.twig');
    }
}
