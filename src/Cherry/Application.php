<?php

namespace Cherry;

use Cherry\Command\AddUnixTime;
use Cherry\Command\GenerateThumbnails;
use Cherry\Command\ImportJomGallery;
use Cherry\Form\ArtWorkType;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Silex\Application as BaseApplication;
use Silex\Application\FormTrait;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

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
            'locale_fallbacks' => ['uk', 'en'],
            'translator.domains' => [],
        ]);
        $this->extend('translator', function(Translator $translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', __DIR__.'/Resources/translations/en.yml', 'en');
            $translator->addResource('yaml', __DIR__.'/Resources/translations/uk.yml', 'uk');

            return $translator;
        });

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
                    'front_end_two_columns' => function (Image $image) {
                        return $image->widen(430);
                    },
                ]
            );
        };

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


        $app->get('/', function () use ($app) {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return $app->redirect('/'.$app['translator']->getLocale());
            }

            $locales = $app['translator']->getFallbackLocales();
            $prefLocales = array_reduce(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']), function ($res, $el) { list($l, $q) = array_merge(explode(';q=', $el), [1]); $res[$l] = (float) $q; return $res; }, []);
            asort($prefLocales);
            $locale = array_reduce(
                array_keys($prefLocales), function ($default, $prefLocale) use ($locales) {
                    return in_array($prefLocale, $locales) ? $prefLocale : $default;
                }, $app['translator']->getLocale()
            );

            return $app->redirect('/'.$locale);
        });

        $localeRouteRequirments = sprintf('^(%s)$', implode('|', $app['locale_fallbacks']));
        $this
            ->get('/{_locale}/art-works/{slug}', 'Cherry\\Controller\\ArtWorksController::viewAction')
            ->bind('art_work')
            ->assert('_locale', $localeRouteRequirments);
        $this
            ->get('/{_locale}/art-works', 'Cherry\\Controller\\ArtWorksController::listAction')
            ->bind('art_works')
            ->assert('_locale', $localeRouteRequirments);
        $this
            ->get('/{_locale}/Tetiana-Cherevan', 'Cherry\\Controller\\MainController::aboutMeAction')
            ->bind('about')
            ->assert('_locale', $localeRouteRequirments);
        $this
            ->get('/{_locale}/', 'Cherry\\Controller\\MainController::homepageAction')
            ->bind('homepage')
            ->assert('_locale', $localeRouteRequirments);
    }
}
