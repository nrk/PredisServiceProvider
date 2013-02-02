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
use Predis\Client;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Exposes a single instance of Predis\Client to Silex.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class PredisServiceProvider implements ServiceProviderInterface
{
    protected static $reserved = array(
        'parameters', 'options', 'default_parameters', 'default_options',
        'clients', 'client_initializer',
    );

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
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        // NOOP
    }

    /**
     * Returns the anonymous function that will be used by the service provider
     * to lazily-initialize new instances of Predis\Client.
     *
     * @param Application $app
     * @param string $prefix
     * @return \Closure
     */
    protected function getClientInitializer(Application $app, $prefix)
    {
        return $app->protect(function ($arguments) use ($app, $prefix) {
            $extract = function ($bag, $key) use ($app, $prefix) {
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
            } else {
                $parameters = $extract($arguments, 'parameters');
                $options = $extract($arguments, 'options');
            }

            return new Client($parameters, $options);
        });
    }

    /**
     * Returns the anonymous function that will be used by the service provider
     * to handle accesses to the root prefix.
     *
     * @param Application $app
     * @param string $prefix
     * @return mixed
     */
    protected function getProviderHandler(Application $app, $prefix)
    {
        return $app->share(function () use ($app, $prefix) {
            $initializer = $app["$prefix.client_initializer"];

            return $initializer($app);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $prefix = $this->prefix;

        $app["$prefix.default_parameters"] = array();
        $app["$prefix.default_options"] = array();
        $app["$prefix.client_initializer"] = $this->getClientInitializer($app, $prefix);
        $app["$prefix"] = $this->getProviderHandler($app, $prefix);
    }
}
