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
use Predis\Client;
use Predis\Profile\Factory as ProfileFactory;

class ClientServiceProviderTest extends ProviderTestCase
{
    protected function getProviderInstance($prefix = 'predis')
    {
        return new ClientServiceProvider($prefix);
    }

    protected function checkRegisteredProvider(Container $app, $prefix)
    {
        $this->assertInstanceOf('Predis\Client', $app[$prefix]);
        $this->assertInternalType('array', $app["$prefix.default_options"]);
        $this->assertInternalType('array', $app["$prefix.default_parameters"]);
        $this->assertInstanceOf('Closure', $app["$prefix.client_initializer"]);
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

    public function testClient()
    {
        $app = $this->register();

        list($parameters, $options) = $this->getParametersAndOptions($app['predis']);

        $this->assertEquals('tcp', $parameters->scheme);
        $this->assertEquals('127.0.0.1', $parameters->host);
        $this->assertEquals(6379, $parameters->port);

        $this->assertEquals(ProfileFactory::getDefault(), $options->profile);
        $this->assertNull($options->prefix);
    }

    public function testClientParametersString()
    {
        $scheme = 'tcp';
        $host = '192.168.1.1';
        $port = 1000;

        $app = $this->register(array(
            'predis.parameters' => "$scheme://$host:$port"
        ));

        list($parameters,) = $this->getParametersAndOptions($app['predis']);

        $this->assertEquals($scheme, $parameters->scheme);
        $this->assertEquals($host, $parameters->host);
        $this->assertEquals($port, $parameters->port);
    }

    public function testClientParametersArray()
    {
        $params = $this->getSomeParameters();

        $app = $this->register(array(
            'predis.parameters' => $params,
        ));

        $this->checkParameters($app, 'predis', $params);
    }

    public function testClientOptions()
    {
        $profile = 'dev';
        $prefix = 'silex:';

        $app = $this->register(array(
            'predis.options' => array(
                'profile' => $profile,
                'prefix' => $prefix,
            ),
        ));

        list(, $options) = $this->getParametersAndOptions($app['predis']);

        $profile = ProfileFactory::get($profile);
        $profile->setProcessor($options->prefix);

        $this->assertEquals($prefix, $options->prefix->getPrefix());
        $this->assertEquals($profile, $options->profile);
    }

    public function testClientCluster()
    {
        $app = $this->register(array(
            'predis.parameters' => array(
                'tcp://127.0.0.1:7001',
                'tcp://127.0.0.1:7002',
                'tcp://127.0.0.1:7003',
            ),
        ));

        $this->assertInstanceOf('Predis\Connection\Aggregate\PredisCluster', $app['predis']->getConnection());
    }
}
