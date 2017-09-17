<?php

namespace Cherry;

class FrontendApplication extends Application
{
    /**
     * {@inheritdoc}
     */
    public function __construct($env = 'prod', array $values = [])
    {
        parent::__construct($env, $values);
        $app = $this;

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
//        $this
//            ->get('/{_locale}/art-works', 'Cherry\\Controller\\ArtWorksController::listAction')
//            ->bind('art_works')
//            ->assert('_locale', $localeRouteRequirments);
        $this
            ->get('/{_locale}/Tetiana-Cherevan', 'Cherry\\Controller\\MainController::aboutMeAction')
            ->bind('about')
            ->assert('_locale', $localeRouteRequirments);
        $this
            ->get('/{_locale}/', 'Cherry\\Controller\\ArtWorksController::listAction')
            ->bind('art_works')
            ->assert('_locale', $localeRouteRequirments);
        $this
            ->get('/{_locale}/cv', 'Cherry\\Controller\\MainController::cvAction')
            ->bind('cv')
            ->assert('_locale', $localeRouteRequirments);
    }
}
