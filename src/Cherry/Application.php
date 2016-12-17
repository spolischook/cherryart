<?php

namespace Cherry;

use Silex\Application as BaseApplication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->register(new \Silex\Provider\TwigServiceProvider(), [
            'twig.path' => __DIR__.'/Resources/views',
        ]);

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
    }
}
