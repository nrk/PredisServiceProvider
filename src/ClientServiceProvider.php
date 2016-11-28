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

use InvalidArgumentException;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Predis\Client;
use Predis\Connection\Parameters;


/**
 * Exposes a single instance of Predis\Client to Silex.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientServiceProvider implements ServiceProviderInterface
{
    protected $prefix;

    /**
     * @param string $prefix Prefix name used to register the service provider in Silex.
     */
    public function __construct($prefix = 'predis')
    {
        if (empty($prefix)) {
            throw new InvalidArgumentException('The specified prefix is not valid.');
        }

        $this->prefix = $prefix;
    }

    /**
     * Returns an anonymous function used by the service provider initialize
     * lazily new instances of Predis\Client.
     *
     * @param Container $app
     * @param string    $prefix
     *
     * @return \Closure
     */
    protected function getClientInitializer(Container $app, $prefix)
    {
        return $app->protect(function ($args) use ($app, $prefix) {
            $extract = function ($bag, $key) use ($app, $prefix) {
                $default = "default_$key";

                if ($bag instanceof Container) {
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

            if (isset($args['parameters']) && is_string($args['parameters'])) {
                $args['parameters'] = $app["$prefix.uri_parser"]($args['parameters']);
            }

            $parameters = $extract($args, 'parameters');
            $options = $extract($args, 'options');

            return $app["$prefix.client_constructor"]($parameters, $options);
        });
    }

    /**
     * Returns an anonymous function used by the service provider to handle
     * accesses to the root prefix.
     *
     * @param Container $app
     * @param string    $prefix
     *
     * @return mixed
     */
    protected function getProviderHandler(Container $app, $prefix)
    {
        return function () use ($app, $prefix) {
            $initializer = $app["$prefix.client_initializer"];

            return $initializer($app);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $prefix = $this->prefix;

        $app["$prefix.default_parameters"] = array();
        $app["$prefix.default_options"] = array();

        $app["$prefix.uri_parser"] = $app->protect(function ($uri) {
            return Parameters::parse($uri);
        });

        $app["$prefix.client_constructor"] = $app->protect(function ($parameters, $options) {
            return new Client($parameters, $options);
        });

        $app["$prefix.client_initializer"] = $this->getClientInitializer($app, $prefix);
        $app["$prefix"] = $this->getProviderHandler($app, $prefix);
    }
}
