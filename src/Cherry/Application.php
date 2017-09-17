<?php

namespace Cherry;

use Cherry\Repository\NewsRepository;
use Doctrine\DBAL\Connection;
use Intervention\Image\Image;
use Silex\Application as BaseApplication;
use Silex\Application\FormTrait;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication
{
    use FormTrait;

    const APPLICATION_PATH = __DIR__;

    /** @var Connection */
    static public $db;

    private $configPath = __DIR__.'/../../config/parameters_{env}.yml';

    private function configure(Application $app, string $env)
    {
        $config = str_replace('{env}', $env, $this->configPath);

        if (!is_file($config)) {
            throw new FileNotFoundException(sprintf('Config file "%s" not found', $config));
        }

        $values = Yaml::parse(file_get_contents($config));
        foreach ($values['parameters'] as $key => $value) {
            $app[$key] = str_replace('%application_path%', self::APPLICATION_PATH, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __construct($env = 'prod', array $values = [])
    {
        parent::__construct($values);
        $app = $this;
        $this->configure($app, $env);

        $this->register(new \Silex\Provider\TwigServiceProvider(), [
            'twig.path' => __DIR__.'/Resources/views',
            'twig.form.templates' => ['bootstrap_3_layout.html.twig'],
        ]);

        $app->register(new \Silex\Provider\MonologServiceProvider(), array(
            'monolog.logfile' => __DIR__.'/../../logs/dev.log',
        ));

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
                'path'     => realpath($app['db_path']),
            ),
        ));
        $this['repository_news'] = function ($app) {
            return new NewsRepository($app['db']);
        };

        self::$db = $app['db'];

        $this['image_handler'] = function ($app) {
            return new ImageHandler(
                realpath(__DIR__.'/../../image_originals'),
                realpath(__DIR__.'/../../images'),
                $app['web_image_domain'],
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
