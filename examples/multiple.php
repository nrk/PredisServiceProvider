<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Predis\Silex\ClientsServiceProvider(), array(
    'predis.clients' => array(
        'first' => 'tcp://127.0.0.1:6379',
        'second' => array(
            'host' => '127.0.0.1',
            'port' => 6380,
        ),
        'third' => array(
            'parameters' => array(
                'host' => '127.0.0.1',
                'port' => 6381,
            ),
            'options' => array(
                'profile' => '2.2',
                'prefix' => 2.4,
            ),
        ),
    ),
    'predis.default_client' => 'first',
    'predis.default_parameters' => array(
        'timeout' => 2.0,
        'read_write_timeout' => 2.0,
    ),
    'predis.default_options' => array(
        'connections' => array(
            'tcp'  => 'Predis\Connection\PhpiredisStreamConnection',
            'unix' => 'Predis\Connection\PhpiredisStreamConnection',
        ),
    ),
));

/** routes **/

$app->get('/', function () use ($app) {
    $default = var_export($app['predis']->info('server'), true);

    $first = var_export($app['predis']['first']->info('server'), true);
    $second = var_export($app['predis']['second']->info('server'), true);
    $third = var_export($app['predis']['third']->info('server'), true);

    return "$default<br/>\n$first<br/>\n$second<br/>\n$third<br/>\n";
});

/** run application **/
$app->run();
