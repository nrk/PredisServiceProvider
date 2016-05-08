<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Silex;

use PHPUnit_Framework_TestCase as StandardTestCase;
use Pimple\Container;
use Predis\Client;

/**
 *
 */
abstract class ProviderTestCase extends StandardTestCase
{
    abstract protected function getProviderInstance($prefix = 'predis');

    protected function register(Array $arguments = array(), ClientServiceProvider $provider = null)
    {
        $app = new Container();

        $app->register($provider ?: $this->getProviderInstance(), $arguments);

        return $app;
    }

    protected function getSomeParameters()
    {
        return array(
            'scheme' => 'tcp',
            'host' => '192.168.1.1',
            'port' => 1000
        );
    }

    protected function getParametersAndOptions(Client $client)
    {
        $parameters = $client->getConnection()->getParameters();
        $options = $client->getOptions();

        return array($parameters, $options);
    }

    protected function checkParameters(Container $container, $clientID, $parameters)
    {
        list($params,) = $this->getParametersAndOptions($container[$clientID]);

        foreach ($parameters as $k => $v) {
            $this->assertSame($v, $params->{$k});
        }
    }

    public function testProviderRegistration()
    {
        $app = $this->register();

        $this->checkRegisteredProvider($app, 'predis');
    }

    public function testPrefixProviderRegistration()
    {
        $prefix = 'my_predis';
        $app = $this->register(array(), $this->getProviderInstance($prefix));

        $this->checkRegisteredProvider($app, $prefix);
    }
}
