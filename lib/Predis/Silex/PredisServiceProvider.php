<?php

/*
 * This file is part of the PredisServiceProvider package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Predis\Client;
use Predis\ClientOptions;
use Predis\ConnectionParameters;

/**
 * Exposes one or more client instances of Predis to Silex.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PredisServiceProvider implements ServiceProviderInterface
{
    protected static $reserved = array(
        'parameters',
        'options',
        'class_path',
        'clients',
    );

    protected $prefix;

    /**
     * @param string $predix Prefix name used to register the service provider in Silex.
     */
    public function __construct($prefix = 'predis')
    {
        if (empty($prefix)) {
            throw new \InvalidArgumentException('The specified prefix is not valid.');
        }

        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $prefix = $this->prefix;

        if (isset($app["$prefix.class_path"])) {
            $app['autoloader']->registerNamespace('Predis', $app["$prefix.class_path"]);
        }

        if (!isset($app["$prefix.default_parameters"])) {
            $app["$prefix.default_parameters"] = array();
        }

        if (!isset($app["$prefix.default_options"])) {
            $app["$prefix.default_options"] = array();
        }

        $app["$prefix.client_initializer"] = $app->protect(function($arguments) use($app, $prefix) {
            $extract = function($bag, $key) use ($app, $prefix) {
                $default = "default_$key";
                if ($bag instanceof Application) {
                    $key = "$prefix.$key";
                }
                if (!isset($bag[$key])) {
                    return $app["$prefix.$default"];
                }
                if (is_array($bag[$key])) {
                    return array_merge($app["$prefix.$default"], $bag[$key]);
                }

                return $bag[$key];
            };

            if (is_string($arguments)) {
                $parameters = $arguments;
                $options = $app["$prefix.default_options"];
            }
            else {
                $parameters = $extract($arguments, 'parameters');
                $options = $extract($arguments, 'options');
            }

            return new Client($parameters, $options);
        });

        if (isset($app["$prefix.clients"])) {
            foreach ($app["$prefix.clients"] as $alias => $args) {
                if (in_array($alias, self::$reserved, true)) {
                    throw new \InvalidArgumentException("The specified alias '$alias' is not valid.");
                }

                $app["$prefix.$alias"] = $app->share(function() use($app, $prefix, $args) {
                    $initializer = $app["$prefix.client_initializer"];

                    if (!isset($args['parameters'])) {
                        if (!isset($args['options']) && !isset($args['default'])) {
                            $args = array('parameters' => $args);
                        }
                    }

                    return $initializer($args);
                });

                if (is_array($args) && isset($args['default']) && $args['default'] == true) {
                    $app[$prefix] = $app->share(function() use($app, $prefix, $alias) {
                        return $app["$prefix.$alias"];
                    });
                }
            }
        }
        else {
            $app[$prefix] = $app->share(function() use($app, $prefix) {
                $initializer = $app["$prefix.client_initializer"];

                return $initializer($app);
            });
        }
    }
}
