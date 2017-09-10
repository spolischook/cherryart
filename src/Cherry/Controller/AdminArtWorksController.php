<?php

namespace Cherry\Controller;

use Cherry\Application;
use Cherry\ImageHandler;
use Cherry\Model\ArtWork;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminArtWorksController
{
    public function listAction(Application $app)
    {
        return $app['twig']->render('Admin/ArtWorks/list.html.twig', [
            'works' => $app['db']->fetchAll('SELECT * FROM `art_works` ORDER BY `date_unix` DESC'),
        ]);
    }

    public function editAction($slug, Application $app, Request $request)
    {
        $work = ArtWork::findOneBySlug($slug);

        if (!$work) {
            throw new NotFoundHttpException(sprintf('Art work with "%s" slug not found', $slug));
        }

        /** @var Form $form */
        $form = $app['form.factory']->create('art_work_type', $work);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var ArtWork $data */
            $data = $form->getData();
            $data->save();
            $app['session']->getFlashBag()->add('success', sprintf('"%s" was updated', $data->getTitleEn()));

            return $app->redirect($app["url_generator"]->generate("admin_art_works"));
        }

        return $app['twig']->render('Admin/ArtWorks/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function createAction(Application $app, Request $request)
    {
        /** @var Form $form */
        $form = $app['form.factory']->create('art_work_type', new ArtWork());
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var ArtWork $artWork */
            $artWork = $form->getData();
            $artWork->save();
            $app['session']->getFlashBag()->add('success', 'New Art work was created');

            return $app->redirect($app["url_generator"]->generate("admin_art_works"));
        }

        return $app['twig']->render('Admin/ArtWorks/create.html.twig', [
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

        return new Response(null, 204);
    }

    public function searchAction(Application $app, Request $request)
    {
        $searchTitle = $request->get('title');
        $utf8Ucfirst = function ($str) {
            $fc = mb_strtoupper(mb_substr($str, 0, 1));
            return $fc.mb_substr($str, 1);
        };

        return new JsonResponse($app['db']->fetchAll(
            sprintf(
                "SELECT * FROM art_works WHERE title_en LIKE '%%%s%%' OR title_en LIKE '%%%s%%' OR title_uk LIKE '%%%s%%'  OR title_uk LIKE '%%%s%%'",
                mb_strtolower($searchTitle),
                $utf8Ucfirst(mb_strtolower($searchTitle)),
                mb_strtolower($searchTitle),
                $utf8Ucfirst(mb_strtolower($searchTitle)))
        ));
    }
}
