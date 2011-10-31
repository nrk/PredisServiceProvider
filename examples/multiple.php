<?php

define('__VENDOR__', __DIR__.'/../vendor');

// See https://github.com/fabpot/Silex/pull/67
require 'phar://'.__VENDOR__.'/Silex/silex.phar/autoload.php';

$app = new Silex\Application();

/** bootstrap **/
$app['autoloader']->registerNamespaces(array(
    'Predis\Silex' => __VENDOR__.'/PredisServiceProvider/lib',
));

$app->register(new Predis\Silex\PredisServiceProvider(array(
        'predis.first' => array(
            'host'      => '127.0.0.1',
            'port'      => 6379,
            'database'  => 0,
        ),
        'predis.second' => array(
            'host'      => '127.0.0.1',
            'port'      => 6379,
            'database'  => 1,
        ),
        'predis.third' => array(
            'host'      => '127.0.0.1',
            'port'      => 6380,
        ),
    ), array(
        'profile' => '2.2',
        'prefix'  => 'silex:',
    )), array(
    'rdb.class_path' => __VENDOR__.'/Predis/lib',
));

/** routes **/
$app->get('/', function() use($app) {
    var_export($app['predis.first']->info(), true);
    var_export($app['predis.second']->info(), true);
    var_export($app['predis.third']->info(), true);

    return '';
});

/** run application **/
$app->run();