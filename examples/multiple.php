<?php

define('__VENDOR__', __DIR__.'/../vendor');

// See https://github.com/fabpot/Silex/pull/67
require 'phar://'.__VENDOR__.'/Silex/silex.phar/autoload.php';

$app = new Silex\Application();

/** bootstrap **/
$app['autoloader']->registerNamespaces(array(
    'Predis\Silex' => __VENDOR__.'/PredisServiceProvider/lib',
));

$app->register(new Predis\Silex\PredisServiceProvider(), array(
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
    'predis.class_path' => __DIR__.'/../predis/lib',
));

/** routes **/
$app->get('/', function() use($app) {
    $first = var_export($app['predis.first']->info(), true);
    $second = var_export($app['predis.second']->info(), true);
    $third = var_export($app['predis.third']->info(), true);

    return "$first<br/>\n$second<br/>\n$third<br/>\n";
});

/** run application **/
$app->run();