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
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->register(new \Silex\Provider\TwigServiceProvider(), [
            'twig.path' => __DIR__.'/Resources/views',
        ]);

        $this->get('/art-works/{slug}', 'Cherry\\Controller\\ArtWorksController::viewAction')->bind('art_work');
        $this->get('/art-works', 'Cherry\\Controller\\ArtWorksController::listAction')->bind('art_works');
        $this->get('/about', 'Cherry\\Controller\\MainController::aboutAction')->bind('about');
        $this->get('/', 'Cherry\\Controller\\MainController::homepageAction')->bind('homepage');
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if ($this['debug'] === true) {
            return parent::handle($request, $type, false);
        }

        return parent::handle($request, $type, true);
    }
}
