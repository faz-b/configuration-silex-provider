<?php

namespace Fazb\Silex\Configuration;

use Silex\Application;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Pimple\Container;
use Fazb\Silex\Configuration\Configuration;

class ConfigurationServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['config'] = function ($app) {
            $configuration = new Configuration($app['site.config']);

            return $configuration;
        };

        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            $twig->addGlobal('config', $app['config']);

            return $twig;
        });
    }

    protected function registerControllers(Container $app)
    {
        $settings = $app['config']->get('settings');
        foreach ($settings as $route => $params) {
            if (isset($params['route'])) {
                $action = isset($params['route']['controller']) ? $params['route']['controller'] : null;
                if (!$action && isset($params['view']['template'])) {
                    $template   = $params['view']['template'];
                    $action     = function () use ($app, $template) {
                        return $app['twig']->render($template);
                    };
                }

                $controller = $app['controllers']->match($params['route']['pattern'], $action);
                $controller->bind($route);
                if (isset($params['route']['method'])) {
                    $controller->method($params['route']['method']);
                }
                if (isset($params['route']['assert'])) {
                    foreach ($params['route']['assert'] as $assert) {
                        $controller->assert(key($assert), current($assert));
                    }
                }
                if (isset($params['route']['value'])) {
                    $controller->value(key($params['route']['value']), current($params['route']['value']));
                }
                if (isset($params['route']['convert'])) {
                    $controller->convert(key($params['route']['convert']), current($params['route']['convert']));
                }
                if (isset($params['route']['i18n'])) {
                    $controller->setOption('i18n', $params['route']['i18n']);
                }
            }
        }
    }

    public function boot(Application $app)
    {
        $dispatcher = $app['dispatcher'];
        $dispatcher->addSubscriber($app['config']);
        $this->registerControllers($app);
    }
}
