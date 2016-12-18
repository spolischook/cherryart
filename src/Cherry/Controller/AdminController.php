<?php

namespace Cherry\Controller;

use Cherry\Application;
use Cherry\Form\ArtWorkType;

class AdminController
{
    public function dashboardAction(Application $app)
    {
        return $app['twig']->render('Admin/dashboard.html.twig');
    }

    public function listArtWorks(Application $app)
    {
        return $app['twig']->render('Admin/artWorks.html.twig', [
            'works' => [
                [
                    'id' => 1,
                    'preview' => '/pictures/works/admin-preview/he-did-not-come.jpg',
                    'title' => 'Він не прийшов',
                    'price' => '1000$',
                    'in_stock' => true,
                ],
                [
                    'id' => 2,
                    'preview' => '/pictures/works/admin-preview/lotuses.jpg',
                    'title' => 'Лотуси',
                    'price' => '1000$',
                    'in_stock' => false,
                ],
                [
                    'id' => 3,
                    'preview' => '/pictures/works/admin-preview/strange-flowers.jpg',
                    'title' => 'Дивні квіти',
                    'price' => '1000$',
                    'in_stock' => true,
                ],
            ]
        ]);
    }

    public function createArtWork(Application $app)
    {
        return $app['twig']->render('Admin/artWorkCreate.html.twig', [
            'form' => $app->form([], [], ArtWorkType::class)->getForm()->createView(),
        ]);
    }
}
