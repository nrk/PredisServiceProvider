# PredisServiceProvider #

This service provider for the __[Silex](http://silex-project.org)__ microframework enables developers to easily use
__[Predis](http://github.com/nrk/predis)__ in their applications to connect to __[Redis](http://redis.io)__.

__NOTE__: starting from [commit 5a20c1c](http://github.com/fabpot/Silex/commit/5a20c1cc13081f6062bd865c1646b48732e00dba),
the developers behind Silex decided to switch from the concept of _extensions_ to the one of _service providers_
and renamed all the interfaces and classes involved by this change. __PredisExtension__ has been renamed to
__PredisServiceProvider__ with the release of __v0.2.0__ to accomodate this change, so if you need to use it
with older versions of Silex you can download the previous __v0.1.0__.


## Getting started ##

Starting to use this service provider is quite easy. Supposing that you already have the scheleton of your Silex
application ready, you just need to register the `Predis\Silex` namespace to point to the path of where the source
code of the provider has been placed and then add an instance to the application object:

``` php
<?php
/* ... */
$app['autoloader']->registerNamespaces(array(
    'Predis\Silex' => __DIR__.'/../vendor/PredisServiceProvider/lib',
));

$app->register(new Predis\Silex\PredisServiceProvider(), array(
    'predis.class_path' => __DIR__.'/../vendor/Predis/lib',
    'predis.parameters' => 'tcp://127.0.0.1:6379/',
    'predis.options'    => array('profile' => '2.2'),
));
/* ... */
```

The `predis.class_path` option lets you specify where to look for Predis. Both `predis.parameters` and
`predis.options` are optional and they accept the same values of the constructor method of `Predis\Client`.

It is also possible to define multiple clients identified by aliases with their own parameters and options
using `predis.clients`. Each client instance will be initialized lazily upon first access:

``` php
<?php
/* ... */
$app->register(new Predis\Silex\PredisServiceProvider(), array(
    'predis.class_path' => __DIR__.'/../vendor/Predis/lib',
    'predis.clients' => array(
        'first' => 'tcp://127.0.0.1:6379',
        'second' => array(
            'host' => '127.0.0.1',
            'port' => 6380,
        ),
        'third' => array(
            'parameters' => 'tcp://127.0.0.1:6381',
            'options' => array(
                'profile' => 'dev',
                'prefix' => 'silex:',
            ),
        ),
    ),
));
/* ... */
```

If you are looking for simple but complete examples of how to use this extension you can have a look at the
_examples_ directory that is included in the repository.


## Dependencies ##

- PHP >= 5.3.2
- Predis >= 0.7.0-dev


## Project links ##
- [Source code](http://github.com/nrk/PredisServiceProvider)
- [Issue tracker](http://github.com/nrk/PredisServiceProvider/issues)


## Author ##

- [Daniele Alessandri](mailto:suppakilla@gmail.com) ([twitter](http://twitter.com/JoL1hAHN))


## Contributors ##

- Jérôme Macias ([github](http://github.com/jeromemacias))


## License ##

The code for PredisServiceProvider is distributed under the terms of the __MIT license__ (see LICENSE).
