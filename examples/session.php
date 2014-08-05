<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Predis\Silex\ClientsServiceProvider(), array(
    'predis.clients' => array(
        'db' => 'tcp://127.0.0.1',
        'session' => array(
            'parameters' => 'tcp://127.0.0.1',
            'options' => array(
                'prefix' => 'sessions:'
            ),
        ),
    ),
));

$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.handler' => $app->share(function () use ($app) {
        $client = $app['predis']['session'];
        $options = array('gc_maxlifetime' => 300);

        // NOTE: Predis\Session\SessionHandler was added in Predis v0.8.2.
        return new Predis\Session\SessionHandler($client, $options);
    })
));

$app->get('/', function () use ($app) {
    $app['session']->set('foo', mt_rand());

    return print_r($app['predis']['db']->keys('sessions:*'), true);
});

$app->run();
