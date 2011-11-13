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
use Predis\Client;
use Predis\Profiles\ServerProfile;

class PredisServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    protected function register(Array $arguments = array(), PredisServiceProvider $provider = null)
    {
        $app = new Application();
        $app->register($provider ?: new PredisServiceProvider(), $arguments);

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

    protected function sharedRegistrationTests(Application $app, $prefix)
    {
        $this->assertInstanceOf('Predis\Client', $app[$prefix]);
        $this->assertInternalType('array', $app["$prefix.default_options"]);
        $this->assertInternalType('array', $app["$prefix.default_parameters"]);
        $this->assertInstanceOf('Closure', $app["$prefix.client_initializer"]);
    }

    protected function sharedCheckParameters(Application $app, $clientID, $parameters)
    {
        list($params,) = $this->getParametersAndOptions($app[$clientID]);

        foreach ($parameters as $k => $v) {
            $this->assertEquals($v, $params->{$k});
        }
    }

    public function testRegistration()
    {
        $app = $this->register();

        $this->sharedRegistrationTests($app, 'predis');
    }

    public function testCustomPrefixRegistration()
    {
        $prefix = 'my_predis';
        $app = $this->register(array(), new PredisServiceProvider($prefix));

        $this->sharedRegistrationTests($app, $prefix);
    }

    public function testSingleClient()
    {
        $app = $this->register();

        list($parameters, $options) = $this->getParametersAndOptions($app['predis']);

        $this->assertEquals('tcp', $parameters->scheme);
        $this->assertEquals('127.0.0.1', $parameters->host);
        $this->assertEquals(6379, $parameters->port);

        $this->assertEquals(ServerProfile::getDefault(), $options->profile);
        $this->assertNull($options->prefix);
    }

    public function testSingleClientParametersString()
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

    public function testSingleClientParametersArray()
    {
        $params = $this->getSomeParameters();

        $app = $this->register(array(
            'predis.parameters' => $params,
        ));

        $this->sharedCheckParameters($app, 'predis', $params);
    }

    public function testSingleClientOptions()
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

        $profile = ServerProfile::get($profile);
        $profile->setProcessor($options->prefix);

        $this->assertEquals($prefix, $options->prefix->getPrefix());
        $this->assertEquals($profile, $options->profile);
    }

    public function testSingleClientCluster()
    {
        $app = $this->register(array(
            'predis.parameters' => array(
                'tcp://127.0.0.1:7001',
                'tcp://127.0.0.1:7002',
                'tcp://127.0.0.1:7003',
            ),
        ));

        $this->assertInstanceOf('Predis\Network\PredisCluster', $app['predis']->getConnection());
    }

    public function testMultiClientsIndexed()
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

        $this->sharedCheckParameters($app, 'predis.0', $params);
        $this->sharedCheckParameters($app, 'predis.1', $params);
        $this->sharedCheckParameters($app, 'predis.2', $params);
        $this->sharedCheckParameters($app, 'predis.3', $params);

        list(, $options) = $this->getParametersAndOptions($app['predis.0']);
        $this->assertEquals(ServerProfile::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis.1']);
        $this->assertEquals(ServerProfile::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis.2']);
        $this->assertEquals(ServerProfile::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis.3']);
        $this->assertEquals(ServerProfile::getDevelopment(), $options->profile);
    }

    public function testMultiClientsAliased()
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

        $this->sharedCheckParameters($app, 'predis.1st', $params);
        $this->sharedCheckParameters($app, 'predis.2nd', $params);
        $this->sharedCheckParameters($app, 'predis.3rd', $params);
        $this->sharedCheckParameters($app, 'predis.4th', $params);

        list(, $options) = $this->getParametersAndOptions($app['predis.1st']);
        $this->assertEquals(ServerProfile::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis.2nd']);
        $this->assertEquals(ServerProfile::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis.3rd']);
        $this->assertEquals(ServerProfile::getDefault(), $options->profile);

        list(, $options) = $this->getParametersAndOptions($app['predis.4th']);
        $this->assertEquals(ServerProfile::getDevelopment(), $options->profile);
    }

    public function testMultiClientsDefault()
    {
        $app = $this->register(array(
            'predis.clients' => array(
                '1st' => array('parameters' => array('port' => 1)),
                '2nd' => array('parameters' => array('port' => 2), 'default' => true),
                '3rd' => array('parameters' => array('port' => 3), 'default' => false),
            )
        ));

        $this->assertSame($app['predis'], $app['predis.2nd']);

        list($parameters,) = $this->getParametersAndOptions($app['predis']);
        $this->assertEquals(2, $parameters->port);
    }


    public function testMultiClientsCluster()
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

        $this->assertInstanceOf('Predis\Network\PredisCluster', $app['predis.1st']->getConnection());
        $this->assertInstanceOf('Predis\Network\PredisCluster', $app['predis.2nd']->getConnection());
        $this->assertInstanceOf('Predis\Network\PredisCluster', $app['predis.3rd']->getConnection());
    }
}
