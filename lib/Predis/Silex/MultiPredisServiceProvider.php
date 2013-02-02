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

use Pimple;
use Silex\Application;

/**
 * Exposes multiple and separate instances of Predis\Client to Silex.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class MultiPredisServiceProvider extends PredisServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderHandler(Application $app, $prefix)
    {
        return $app->share(function () use ($app, $prefix) {
            $clients = new Pimple();

            foreach ($app["$prefix.clients"] as $alias => $args) {
                $clients[$alias] = $clients->share(function () use ($app, $prefix, $args) {
                    $initializer = $app["$prefix.client_initializer"];

                    if (is_string($args)) {
                        $args = array('parameters' => $args);
                    } else if (!isset($args['parameters']) && !isset($args['options'])) {
                        $args = array('parameters' => $args);
                    }

                    return $initializer($args);
                });
            }

            return $clients;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app["{$this->prefix}.clients"] = array();

        parent::register($app);
    }
}
