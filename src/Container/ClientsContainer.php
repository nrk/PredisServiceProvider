<?php

/*
 * This file is part of the PredisServiceProvider package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Silex\Container;

use BadMethodCallException;
use Pimple;
use Silex\Application;

/**
 * Specialized Pimple container that supports the definition of a default client
 * responding to `$app['predis']`.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ClientsContainer extends Pimple
{
    protected $application;
    protected $prefix;

    /**
     * {@inheritdoc}
     */
    public function __construct(Application $app, $prefix)
    {
        $this->application = $app;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments)
    {
        if (isset($this->application["{$this->prefix}.default_client"])) {
            $default = $this->application["{$this->prefix}.default_client"];
            $value = call_user_func_array(array($this[$default], $method), $arguments);

            return $value;
        }

        throw new BadMethodCallException("Undefined method `$method`.");
    }
}
