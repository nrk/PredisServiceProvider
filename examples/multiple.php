<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Predis\Silex\MultiPredisServiceProvider(), array(
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
    'predis.default_parameters' => array(
        'timeout' => 2.0,
        'read_write_timeout' => 2.0,
    ),
    'predis.default_options' => array(
        'connections' => array(
            'tcp'  => 'Predis\Connection\PhpiredisConnection',
            'unix' => 'Predis\Connection\PhpiredisConnection',
        ),
    ),
));

/** routes **/

$app->get('/', function () use ($app) {
    $first = var_export($app['predis']['first']->info(), true);
    $second = var_export($app['predis']['second']->info(), true);
    $third = var_export($app['predis']['third']->info(), true);

    return "$first<br/>\n$second<br/>\n$third<br/>\n";
});

/** run application **/
$app->run();
