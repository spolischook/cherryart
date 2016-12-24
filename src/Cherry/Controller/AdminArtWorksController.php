<?php

namespace Cherry\Controller;

use Cherry\Application;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminArtWorksController
{
    public function listAction(Application $app)
    {
        return $app['twig']->render('Admin/artWorks.html.twig', [
            'works' => $app['db']->fetchAll('SELECT * FROM `art_works` ORDER BY `date_unix` DESC'),
        ]);
    }

    public function editAction($slug, Application $app, Request $request)
    {
        $sql = "SELECT * FROM art_works WHERE slug = ?";
        $work = $app['db']->fetchAssoc($sql, [$slug]);

        if (!$work) {
            throw new NotFoundHttpException(sprintf('Art work with "%s" slug not found', $slug));
        }

        /** @var Form $form */
        $form = $app['form.factory']->create('art_work_type', $work);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $app['db']->update('art_works', $data, ['slug' => $data['slug']]);
            $app['session']->getFlashBag()->add('success', sprintf('"%s" was updated', $work['title_en']));

            return $app->redirect($app["url_generator"]->generate("admin_art_works"));
        }

        return $app['twig']->render('Admin/artWorkEdit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function createAction(Application $app, Request $request)
    {
        /** @var Form $form */
        $form = $app['form.factory']->create('art_work_type');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $app['db']->insert('art_works', $data);
            $app['session']->getFlashBag()->add('success', 'New Art work was created');

            return $app->redirect($app["url_generator"]->generate("admin_art_works"));
        }

        return $app['twig']->render('Admin/artWorkCreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function removeImageAction($slug, $imageFileNameForDelete, Application $app)
    {
        $original = $app['image_handler']->getOriginal($imageFileNameForDelete, ImageHandler::TYPE_ART_WORK);

        if (!is_file($original)) {
            throw new NotFoundHttpException(sprintf('Image with "%s" name not found', $imageFileNameForDelete));
        }

        $sql = "SELECT * FROM art_works WHERE slug = ?";
        $work = $app['db']->fetchAssoc($sql, [$slug]);

        $work['images'] = implode(',', array_filter(
            explode(',', $work['images']),
            function ($imageName) use ($imageFileNameForDelete) {
                return $imageName !== $imageFileNameForDelete;
            }
        ));

        $app['db']->update('art_works', $work, ['slug' => $work['slug']]);

        return new Response('', 204);
    }
}
