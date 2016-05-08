<?php

require __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Predis\Silex\ClientServiceProvider(), array(
    'predis.parameters' => [
        'tcp://127.0.0.1:6379?alias=node-01',
        'tcp://127.0.0.1:6380?alias=node-02',
        'tcp://127.0.0.1:6381?alias=node-03',
    ],
    'predis.options'    => array(
        'profile' => '3.0',
        'prefix'  => 'silex:',
    ),
));

/** routes **/
$app->get('/', function () use ($app) {
    $cmdINFO = $app['predis']->createCommand('INFO', array('server'));
    $nodes = $app['predis']->getConnection()
                           ->executeCommandOnNodes($cmdINFO);

    return var_export($nodes, true);
});

/** run application **/
$app->run();
