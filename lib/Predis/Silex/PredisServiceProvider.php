<?php

namespace Predis\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Predis\Client;
use Predis\ClientOptions;
use Predis\ConnectionParameters;

class PredisServiceProvider implements ServiceProviderInterface
{
    protected static $invalidAliases = array(
        'parameters',
        'options',
        'class_path',
        'clients',
    );

    public function register(Application $app)
    {
        if (isset($app['predis.class_path'])) {
            $app['autoloader']->registerNamespace('Predis', $app['predis.class_path']);
        }

        if (!isset($app['predis.default_parameters'])) {
            $app['predis.default_parameters'] = array();
        }

        if (!isset($app['predis.default_options'])) {
            $app['predis.default_options'] = array();
        }

        $app['predis.client_initializer'] = $app->protect(function($arguments) use($app) {
            $extract = function($bag, $key) use ($app) {
                $default = "default_$key";
                if ($bag instanceof Application) {
                    $key = "predis.$key";
                }
                if (!isset($bag[$key])) {
                    return $app["predis.$default"];
                }
                if (is_array($bag[$key])) {
                    return array_merge($app["predis.$default"], $bag[$key]);
                }

                return $bag[$key];
            };

            if (is_string($arguments)) {
                $parameters = $arguments;
                $options = $app['predis.default_options'];
            }
            else {
                $parameters = $extract($arguments, 'parameters');
                $options = $extract($arguments, 'options');
            }

            return new Client($parameters, $options);
        });

        if (isset($app['predis.clients'])) {
            foreach ($app['predis.clients'] as $alias => $args) {
                if (in_array($alias, self::$invalidAliases, true)) {
                    throw new \InvalidArgumentException("The specified alias '$alias' is not valid.");
                }

                $app["predis.$alias"] = $app->share(function() use($app, $args) {
                    $initializer = $app['predis.client_initializer'];

                    if (!isset($args['parameters'])) {
                        if (!isset($args['options']) && !isset($args['default'])) {
                            $args = array('parameters' => $args);
                        }
                    }

                    return $initializer($args);
                });

                if (isset($args['default']) && $args['default'] == true) {
                    $app['predis'] = $app["predis.$alias"];
                }
            }
        }
        else {
            $app['predis'] = $app->share(function() use($app) {
                $initializer = $app['predis.client_initializer'];

                return $initializer($app);
            });
        }
    }
}
