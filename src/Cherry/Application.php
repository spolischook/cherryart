<?php

namespace Cherry;

use Cherry\Repository\NewsRepository;
use Doctrine\DBAL\Connection;
use Intervention\Image\Image;
use Silex\Application as BaseApplication;
use Silex\Application\FormTrait;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

class Application extends BaseApplication
{
    use FormTrait;

    /** @var Connection */
    static public $db;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $app = $this;

        $this->register(new \Silex\Provider\TwigServiceProvider(), [
            'twig.path' => __DIR__.'/Resources/views',
            'twig.form.templates' => ['bootstrap_3_layout.html.twig'],
        ]);

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
        $this['repository_news'] = function ($app) {
            return new NewsRepository($app['db']);
        };

        self::$db = $app['db'];

        $this['image_handler'] = function () {
            return new ImageHandler(
                realpath(__DIR__.'/../../image_originals'),
                realpath(__DIR__.'/../../images'),
                'http://images.cherryart.local',
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
    }
}
