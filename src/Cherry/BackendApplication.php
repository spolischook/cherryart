<?php

namespace Cherry;

use Cherry\Command\GenerateThumbnails;
use Cherry\Command\ImportJomGallery;
use Cherry\Form\ArtWorkType;

class BackendApplication extends Application
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $app = $this;

        $this->register(new \Silex\Provider\FormServiceProvider());
        $this->register(new \Silex\Provider\ValidatorServiceProvider());

        $this['thumbnail_generation_command'] = function ($app) {
            return new GenerateThumbnails($app['image_handler']);
        };

        $this['import_jom_gallery_command'] = function ($app) {
            return new ImportJomGallery($app['image_handler'], $app['db']);
        };

        $app['art_work_type'] = function ($app) {
            return new ArtWorkType($app['image_handler']);
        };
        $app->extend('form.types', function ($types) use ($app) {
            $types[] = 'art_work_type';

            return $types;
        });

        $this->register(new \Silex\Provider\SessionServiceProvider());
        $this->register(new \Silex\Provider\SecurityServiceProvider(), [
            'security.firewalls' => [
                'admin' => [
                    'pattern' => '^/admin',
                    'form' => ['login_path' => '/login', 'check_path' => '/admin/login_check'],
                    'logout' => ['logout_path' => '/admin/logout', 'invalidate_session' => true],
                    'users' => [
                        // raw password is foo
                        'admin' => ['ROLE_ADMIN', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a'],
                    ],
                ],
            ],
        ]);
//        $this['security.access_rules'] = [
//            ['^/admin', 'ROLE_ADMIN', 'https'],
//        ];

        $this->get('/login', 'Cherry\\Controller\\SecurityController::loginAction')->bind('login');
        $this->get('/admin/login_check', 'Cherry\\Controller\\SecurityController::homepageAction')->bind('admin_login_check');

        $this
            ->get('/admin', 'Cherry\\Controller\\AdminController::dashboardAction')
            ->bind('admin_dashboard');

        $this
            ->get('/admin/art-works', 'Cherry\\Controller\\AdminArtWorksController::listAction')
            ->bind('admin_art_works');
        $this
            ->match('/admin/art-works/new', 'Cherry\\Controller\\AdminArtWorksController::createAction')
            ->bind('admin_art_works_create')
            ->method('GET|POST');
        $this
            ->match('/admin/art-works/{slug}', 'Cherry\\Controller\\AdminArtWorksController::editAction')
            ->bind('admin_art_works_edit')
            ->method('GET|POST');
        $this
            ->delete('/admin/art-works/{slug}/images/{imageFileNameForDelete}', 'Cherry\\Controller\\AdminArtWorksController::removeImageAction')
            ->bind('admin_art_works_remove_image');

        $this
            ->get('/admin/news', 'Cherry\\Controller\\AdminNewsController::listAction')
            ->bind('admin_news');
        $this
            ->match('/admin/news/new', 'Cherry\\Controller\\AdminNewsController::createAction')
            ->bind('admin_news_create')
            ->method('GET|POST');
        $this
            ->match('/admin/news/{slug}', 'Cherry\\Controller\\AdminNewsController::editAction')
            ->bind('admin_news_edit')
            ->method('GET|POST');
        $this
            ->delete('/admin/news/{slug}/images/{imageFileNameForDelete}', 'Cherry\\Controller\\AdminNewsController::removeImageAction')
            ->bind('admin_news_remove_image');
    }
}
