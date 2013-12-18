# PredisServiceProvider #

This is a service provider for [Silex](http://silex-project.org) that enables developers to easily connect
to [Redis](http://redis.io) by using [Predis](http://github.com/nrk/predis).


## Getting started ##

Supposing that you have already set up the required dependencies using [Composer](http://packagist.org/about-composer)
and the skeleton of your Silex application is ready, now you simply need to register the service provider
specifying the parameters and options needed to access Redis:

```php
$app->register(new Predis\Silex\PredisServiceProvider(), array(
    'predis.parameters' => 'tcp://127.0.0.1:6379',
    'predis.options'    => array('profile' => '2.2'),
));
```

This will register a single `Predis\Client` instance accessible by your application using `$app['predis']`.
Both `predis.parameters` and `predis.options` are optional and accept the same values of the constructor
of `Predis\Client`.

Certain applications might need more than one client to reach different servers or configured with different
options such as key prefixing or server profile. In such cases you must use `Predis\Silex\MultiPredisServiceProvider`
and provide a list of clients with their own parameters and options using `predis.clients`:

```php
$app->register(new Predis\Silex\MultiPredisServiceProvider(), array(
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
```

Client instances will be exposed to your application using `$app['predis'][$alias]` where `$alias` is the key
used to populate the items of `predis.clients`. You can optionally define a default client by specifying its
alias in `predis.default_client` to make it accessible by invoking methods of `Predis\Client` directly  against
`$app['predis']`. Each client instance will be initialized lazily upon first access.

You can find more details on how to use this provider in the `examples` directory or the test suite,


## Dependencies ##

- PHP >= 5.3.2
- Predis >= 0.8.0


## Project links ##
- [Source code](http://github.com/nrk/PredisServiceProvider)
- [Issue tracker](http://github.com/nrk/PredisServiceProvider/issues)


## Author ##

- [Daniele Alessandri](mailto:suppakilla@gmail.com) ([twitter](http://twitter.com/JoL1hAHN))


## Contributors ##

- Jérôme Macias ([github](http://github.com/jeromemacias))


## License ##

The code for PredisServiceProvider is distributed under the terms of the [MIT license](LICENSE).
