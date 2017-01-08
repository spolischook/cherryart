<?php

namespace Cherry;

use Cherry\Command\GenerateThumbnails;
use Cherry\Command\ImportJomGallery;
use Cherry\Command\ImportJoomlaArticles;
use Cherry\Form\ArtWorkType;
use Cherry\Form\NewsExhibitionType;

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

        $this['import_joomla_articles_command'] = function ($app) {
            return new ImportJoomlaArticles($app['image_handler'], $app['db']);
        };

        $app['art_work_type'] = function ($app) {
            return new ArtWorkType($app['image_handler']);
        };
        $app['news_exhibition_type'] = function ($app) {
            return new NewsExhibitionType($app['image_handler'], $app['repository_news']);
        };
        $app->extend('form.types', function ($types) use ($app) {
            $types[] = 'art_work_type';
            $types[] = 'news_exhibition_type';

            return $types;
        });

        $this->register(new \Silex\Provider\SessionServiceProvider());
        $this->register(new \Silex\Provider\SecurityServiceProvider(), [
            'security.firewalls' => [
                'admin' => [
                    'anonymous' => true,
                    'pattern' => '^/',
                    'form' => ['login_path' => '/login', 'check_path' => '/login_check'],
                    'logout' => ['logout_path' => '/logout', 'invalidate_session' => true],
                    'users' => [
                        // raw password is foo
                        'admin' => ['ROLE_ADMIN', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a'],
                    ],
                ],
            ],
        ]);
        $this['security.access_rules'] = [
            ['^/login', 'IS_AUTHENTICATED_ANONYMOUSLY'],
            ['^/(?!login$)', 'ROLE_ADMIN'],
//            ['^/login', 'ROLE_ADMIN', 'https'],
//            ['^/', 'ROLE_ADMIN', 'https'],
        ];

        $this->get('/login', 'Cherry\\Controller\\SecurityController::loginAction')->bind('login');
        $this->get('/login_check', 'Cherry\\Controller\\SecurityController::homepageAction')->bind('admin_login_check');

        $this
            ->get('/', 'Cherry\\Controller\\AdminController::dashboardAction')
            ->bind('admin_dashboard');

        $this
            ->get('/art-works', 'Cherry\\Controller\\AdminArtWorksController::listAction')
            ->bind('admin_art_works');
        $this
            ->match('/art-works/new', 'Cherry\\Controller\\AdminArtWorksController::createAction')
            ->bind('admin_art_works_create')
            ->method('GET|POST');
        $this
            ->get('art-works/search', 'Cherry\\Controller\\AdminArtWorksController::searchAction')
            ->bind('admin_art_works_search');
        $this
            ->match('/art-works/{slug}', 'Cherry\\Controller\\AdminArtWorksController::editAction')
            ->bind('admin_art_works_edit')
            ->method('GET|POST');
        $this
            ->delete('/art-works/{slug}/images/{imageFileNameForDelete}', 'Cherry\\Controller\\AdminArtWorksController::removeImageAction')
            ->bind('admin_art_works_remove_image');

        $this
            ->get('/news', 'Cherry\\Controller\\AdminNewsController::listAction')
            ->bind('admin_news');
        $this
            ->match('/news/new', 'Cherry\\Controller\\AdminNewsController::createAction')
            ->bind('admin_news_create')
            ->method('GET');
        $this
            ->match('/news/new/{type}', 'Cherry\\Controller\\AdminNewsController::createTypedAction')
            ->bind('admin_news_create_type')
            ->method('GET|POST');
        $this
            ->match('/news/{id}', 'Cherry\\Controller\\AdminNewsController::editAction')
            ->bind('admin_news_edit')
            ->method('GET|POST');
        $this
            ->delete('/news/{slug}/images/{imageFileNameForDelete}', 'Cherry\\Controller\\AdminNewsController::removeImageAction')
            ->bind('admin_news_remove_image');
    }
}
