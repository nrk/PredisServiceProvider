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

use Pimple\Container;
use Predis\Silex\Container\ClientsContainer;

/**
 * Exposes multiple instances of Predis\Client to Silex.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientsServiceProvider extends ClientServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderHandler(Container $app, $prefix)
    {
        return function () use ($app, $prefix) {
            $clients = $app["{$prefix}.clients_container"]($prefix);

            foreach ($app["$prefix.clients"] as $alias => $args) {
                $clients[$alias] = function () use ($app, $prefix, $args) {
                    $initializer = $app["$prefix.client_initializer"];

                    if (is_string($args)) {
                        $args = array('parameters' => $args);
                    } elseif (!isset($args['parameters']) && !isset($args['options'])) {
                        $args = array('parameters' => $args);
                    }

                    return $initializer($args);
                };
            }

            return $clients;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app["{$this->prefix}.clients"] = array();

        $app["{$this->prefix}.clients_container"] = $app->protect(function ($prefix) use ($app) {
            return new ClientsContainer($app, $prefix);
        });

        parent::register($app);
    }
}
