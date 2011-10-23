<?php

namespace Predis\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Predis\Client;
use Predis\ClientOptions;
use Predis\ConnectionParameters;

class PredisServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['rdb.default_parameters'] = array(
            'host' => 'localhost',
            'port' => '6379',
        );

        $app['rdb.default_options'] = array(
            'profile' => '2.4',
        );

        $app['rdbs.parameters.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['rdbs.parameters'])) {
                $app['rdbs.parameters'] = array('default' => isset($app['rdb.parameters']) ? $app['rdb.parameters'] : array());
            }

            $tmp = $app['rdbs.parameters'];
            foreach ($tmp as $name => &$parameters) {
                $parameters = array_replace($app['rdb.default_parameters'], $parameters);
                $parameters['alias'] = $name;

                if (!isset($app['rdbs.default'])) {
                    $app['rdbs.default'] = $name;
                }
            }

            $app['rdbs.parameters'] = $tmp;
            $app['rdb.options'] = array_replace($app['rdb.default_options'], $app['rdb.options']);
        });

        $app['rdbs'] = $app->share(function () use ($app) {
            $app['rdbs.parameters.initializer']();

            $connectionParameters = array();
            foreach ($app['rdbs.parameters'] as $name => $parameters) {
                $connectionParameters[] = new ConnectionParameters($parameters);
            }

            $options = new ClientOptions($app['rdb.options']);
            $redis = new Client($connectionParameters, $options);

            $rdbs = new \Pimple();
            foreach ($connectionParameters as $config) {
                $name = $config->alias;
                $rdbs[$name] = $redis->getClientFor($name);
            }

            return $rdbs;
        });

        // shortcuts for the "first" RDB
        $app['rdb'] = $app->share(function() use ($app) {
            $rdbs = $app['rdbs'];

            return $rdbs[$app['rdbs.default']];
        });

        if (isset($app['rdb.class_path'])) {
            $app['autoloader']->registerNamespace('Predis', $app['rdb.class_path']);
        }
    }
}
