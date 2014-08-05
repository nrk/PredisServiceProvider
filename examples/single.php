<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Predis\Silex\ClientServiceProvider(), array(
    'predis.parameters' => 'tcp://127.0.0.1:6379/',
    'predis.options'    => array(
        'profile' => '2.2',
        'prefix'  => 'silex:',
    ),
));

/** routes **/
$app->get('/', function () use ($app) {
    return var_export($app['predis']->info(), true);
});

/** run application **/
$app->run();
