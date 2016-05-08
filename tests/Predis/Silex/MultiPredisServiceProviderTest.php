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
use Predis\Profile\Factory as ProfileFactory;

class ClientsServiceProviderTest extends ProviderTestCase
{
    protected function getProviderInstance($prefix = 'predis')
    {
        return new ClientsServiceProvider($prefix);
    }

    protected function checkRegisteredProvider(Container $app, $prefix)
    {
        $this->assertInstanceOf('Pimple\Container', $app[$prefix]);
        $this->assertInternalType('array', $app["$prefix.default_options"]);
        $this->assertInternalType('array', $app["$prefix.default_parameters"]);
        $this->assertInstanceOf('Closure', $app["$prefix.client_initializer"]);
    }

    public function testClientsIndexed()
    {
        $params = $this->getSomeParameters();

        $app = $this->register(array(
            'predis.clients' => array(
                "{$params['scheme']}://{$params['host']}:{$params['port']}",
                $params,
                array('parameters' => $params),
                array('parameters' => $params, 'options' => array('profile' => 'dev')),
            )
        ));

        $this->checkParameters($app['predis'], 0, $params);
        $this->checkParameters($app['predis'], 1, $params);
        $this->checkParameters($app['predis'], 2, $params);
        $this->checkParameters($app['predis'], 3, $params);

        list(, $options) = $this->getParametersAndOptions($app['predis'][0]);
        $this->assertEquals(ProfileFactory::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis'][1]);
        $this->assertEquals(ProfileFactory::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis'][2]);
        $this->assertEquals(ProfileFactory::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis'][3]);
        $this->assertEquals(ProfileFactory::getDevelopment(), $options->profile);
    }

    public function testClientsAliased()
    {
        $params = $this->getSomeParameters();

        $app = $this->register(array(
            'predis.clients' => array(
                '1st' => "{$params['scheme']}://{$params['host']}:{$params['port']}",
                '2nd' => $params,
                '3rd' => array('parameters' => $params),
                '4th' => array('parameters' => $params, 'options' => array('profile' => 'dev')),
            )
        ));

        $this->checkParameters($app['predis'], '1st', $params);
        $this->checkParameters($app['predis'], '2nd', $params);
        $this->checkParameters($app['predis'], '3rd', $params);
        $this->checkParameters($app['predis'], '4th', $params);

        list(, $options) = $this->getParametersAndOptions($app['predis']['1st']);
        $this->assertEquals(ProfileFactory::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis']['2nd']);
        $this->assertEquals(ProfileFactory::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis']['3rd']);
        $this->assertEquals(ProfileFactory::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis']['4th']);
        $this->assertEquals(ProfileFactory::getDevelopment(), $options->profile);
    }

    public function testClientsCluster()
    {
        $app = $this->register(array(
            'predis.clients' => array(
                '1st' => array(
                    'tcp://127.0.0.1:7001',
                    'tcp://127.0.0.1:7002',
                ),
                '2nd' => array(
                    'tcp://127.0.0.1:7003',
                    'tcp://127.0.0.1:7004',
                ),
                '3rd' => array(
                    'tcp://127.0.0.1:7005',
                    'tcp://127.0.0.1:7006',
                ),
            ),
        ));

        $this->assertInstanceOf('Predis\Connection\Aggregate\PredisCluster', $app['predis']['1st']->getConnection());
        $this->assertInstanceOf('Predis\Connection\Aggregate\PredisCluster', $app['predis']['2nd']->getConnection());
        $this->assertInstanceOf('Predis\Connection\Aggregate\PredisCluster', $app['predis']['3rd']->getConnection());
    }
}
