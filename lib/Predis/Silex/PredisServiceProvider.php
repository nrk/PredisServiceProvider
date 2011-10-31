<?php

namespace Predis\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Predis\Client;
use Predis\ClientOptions;
use Predis\ConnectionParameters;

class PredisServiceProvider implements ServiceProviderInterface
{
    private $parameters = array();

    private $options = array();

    public function __construct($parameters = array(), array $options = array())
    {
        $this->parameters = $parameters;
        $this->options = $options;
    }

    public function register(Application $app)
    {
        $app['predis.default_parameters'] = array(
            'host' => 'localhost',
            'port' => '6379',
        );

        $app['predis.default_options'] = array(
            'profile' => '2.4',
        );

        $app['predis.options'] = array_replace($app['predis.default_options'], $this->options);

        $tmp = array();
        if (empty($this->parameters) || !is_array($this->parameters)
            || isset($this->parameters['host']) || isset($this->parameters['port'])
        ) {
            if (is_array($this->parameters)) {
                $this->parameters = array_replace($app['predis.default_parameters'], $this->parameters);
            }
            $tmp['predis'] = $this->parameters;

        } else {
            foreach ($this->parameters as $name => &$parameters) {
                if (!is_array($parameters)) {
                    throw new \InvalidArgumentException(sprintf('Parameters for "%s" must be an array.', $name));
                }

                $tmp[$name] = array_replace($app['predis.default_parameters'], $parameters);
                $tmp[$name]['alias'] = $name;
            }
        }
        $app['predis.parameters'] = $tmp;

        $app['predis.client.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            $connectionParameters = array();
            foreach ($app['predis.parameters'] as $name => $parameters) {
                $connectionParameters[] = new ConnectionParameters($parameters);
            }

            $clientOptions = new ClientOptions($app['predis.options']);
            $app['predis.client'] = new Client($connectionParameters, $clientOptions);

        });

        if (1 == count($app['predis.parameters'])) {
            $name = key($tmp = $app['predis.parameters']);

            $app[$name] = $app->share(function() use ($app, $name) {
                $connectionParameters = new ConnectionParameters($app['predis.parameters'][$name]);
                $clientOptions = new ClientOptions($app['predis.options']);

                return new Client($connectionParameters, $clientOptions);
            });

        } else {
            foreach (array_keys($app['predis.parameters']) as $name) {
                $app[$name] = $app->share(function() use ($app, $name) {
                    $app['predis.client.initializer']();

                    return $app['predis.client']->getClientFor($name);
                });
            }
        }

        if (isset($app['predis.class_path'])) {
            $app['autoloader']->registerNamespace('Predis', $app['predis.class_path']);
        }
    }
}
