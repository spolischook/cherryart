<?php

namespace Cherry;

use Cherry\Command\GenerateThumbnails;
use Cherry\Form\ArtWorkType;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Silex\Application as BaseApplication;
use Silex\Application\FormTrait;

class Application extends BaseApplication
{
    use FormTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $app = $this;

        $this->register(new \Silex\Provider\FormServiceProvider());
        $this->register(new \Silex\Provider\TwigServiceProvider(), [
            'twig.path' => __DIR__.'/Resources/views',
            'twig.form.templates' => ['bootstrap_3_layout.html.twig'],
        ]);
        $this->register(new \Silex\Provider\ValidatorServiceProvider());
        $this->register(new \Silex\Provider\LocaleServiceProvider());
        $this->register(new \Silex\Provider\TranslationServiceProvider(), [
            'translator.domains' => [],
        ]);

        $this->register(new \Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'   => 'pdo_sqlite',
                'path'     => realpath(__DIR__.'/../../app.db'),
            ),
        ));

        $this['image_handler'] = function () {
            return new ImageHandler(
                realpath(__DIR__.'/../../image_originals'),
                realpath(__DIR__.'/../../web/images'),
                '/images',
                [
                    'admin' => function (Image $image) {
                        return $image->heighten(70)->crop(70, 70);
                    },
                    'admin_preview' => function (Image $image) {
                        return $image->heighten(150)->crop(150, 150);
                    },
                ]
            );
        };

        $this['thumbnail_generation_command'] = function ($app) {
            return new GenerateThumbnails($app['image_handler']);
        };

        $app['art_work_type'] = function ($app) {
            return new ArtWorkType($app['db'], $app['image_handler']);
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

        $this->get('/art-works/{slug}', 'Cherry\\Controller\\ArtWorksController::viewAction')->bind('art_work');
        $this->get('/art-works', 'Cherry\\Controller\\ArtWorksController::listAction')->bind('art_works');
        $this->get('/about', 'Cherry\\Controller\\MainController::aboutAction')->bind('about');
        $this->get('/', 'Cherry\\Controller\\MainController::homepageAction')->bind('homepage');

        $this->get('/login', 'Cherry\\Controller\\SecurityController::loginAction')->bind('login');
        $this->get('/admin/login_check', 'Cherry\\Controller\\SecurityController::homepageAction')->bind('admin_login_check');

        $this->get('/admin', 'Cherry\\Controller\\AdminController::dashboardAction')->bind('admin_dashboard');
        $this->get('/admin/art-works', 'Cherry\\Controller\\AdminController::listArtWorks')->bind('admin_art_works');
        $this->match('/admin/art-works/new', 'Cherry\\Controller\\AdminController::createArtWork')
            ->bind('admin_create_work')
            ->method('GET|POST');
        $this->match('/admin/art-works/{slug}', 'Cherry\\Controller\\AdminController::editArtWork')
            ->bind('admin_edit_work')
            ->method('GET|POST');
        $this->delete('/admin/art-works/{slug}/images/{imageFileNameForDelete}', 'Cherry\\Controller\\AdminController::removeImage')
            ->bind('admin_edit_work_delete_image');
    }
}
