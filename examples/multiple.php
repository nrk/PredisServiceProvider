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
    'rdb.class_path' => __VENDOR__.'/Predis/lib',
    'rdbs.parameters' => array(
        'first' => array(
            'host'      => '127.0.0.1',
            'port'      => 6379,
            'database'  => 0,
        ),
        'second' => array(
            'host'      => '127.0.0.1',
            'port'      => 6379,
            'database'  => 1,
        ),
        'third' => array(
            'host'      => '127.0.0.1',
            'port'      => 6380,
        ),
    ),
    'rdb.options'    => array(
        'profile' => '2.2',
        'prefix'  => 'silex:',
    ),
));

/** routes **/
$app->get('/', function() use($app) {
    var_export($app['rdbs']['first']->info(), true);
    var_export($app['rdbs']['second']->info(), true);
    var_export($app['rdbs']['third']->info(), true);

    return '';
});

/** run application **/
$app->run();