<?php

namespace Cherry\Controller;

use Cherry\BackendApplication as Application;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminNewsController
{
    public function listAction(Application $app): string
    {
        return $app['twig']->render('Admin/News/list.html.twig', [
            'news' => $app['repository_news']->findAll('SELECT * FROM `news` ORDER BY `date_unix` DESC'),
        ]);
    }

    public function createAction(Application $app, Request $request)
    {
        return $app['twig']->render('Admin/News/create.html.twig', []);
    }

    public function createTypedAction($type, Application $app, Request $request): string
    {
        /** @var Form $form */
        $form = $app['form.factory']->create(sprintf('news_%s_type', $type));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $app['repository_news']->insert($data);
            $app['session']->getFlashBag()->add('success', 'New News item was created');

            return $app->redirect($app["url_generator"]->generate("admin_news"));
        }

        return $app['twig']->render("Admin/News/create_$type.html.twig", [
            'form' => $form->createView(),
        ]);
    }

    public function editAction($id, Application $app, Request $request): string
    {
        $newsItem = $app['repository_news']->find($id);

        if (!$newsItem) {
            throw new NotFoundHttpException(sprintf('Art work with "%s" id not found', $id));
        }

        /** @var Form $form */
        $form = $app['form.factory']->create(sprintf('news_%s_type', $newsItem['type']), $newsItem);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $app['repository_news']->update($data, ['id' => $data['id']]);
            $app['session']->getFlashBag()->add('success', sprintf('"%s" was updated', $newsItem['title_en']));

            return $app->redirect($app["url_generator"]->generate("admin_news"));
        }

        return $app['twig']->render(sprintf('Admin/News/edit_%s.html.twig', $newsItem['type']), [
            'form' => $form->createView(),
        ]);
    }

    public function removeImageAction($slug, $imageFileNameForDelete, Application $app): string
    {

    }
}
