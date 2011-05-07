<?php

namespace Predis\Silex;

use Silex\Application;
use Silex\ExtensionInterface;

use Predis\Client;
use Predis\ClientOptions;
use Predis\ConnectionParameters;

class PredisExtension implements ExtensionInterface
{
    public function register(Application $app)
    {
        $app['predis'] = $app->share(function () use ($app) {
            if (isset($app['predis.class_path'])) {
                $app['autoloader']->registerNamespace('Predis', $app['predis.class_path']);
            }

            $parameters = new ConnectionParameters(isset($app['predis.parameters']) ? $app['predis.parameters'] : array());
            $options = new ClientOptions(isset($app['predis.options']) ? $app['predis.options'] : array());
            $client = new Client($parameters, $options);

            return $client;
        });
    }
}
