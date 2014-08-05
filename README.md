# Predis ServiceProvider #

This service provider for [Silex](http://silex-project.org/) allows developers to easily configure
and expose [Predis](http://github.com/nrk/predis) enabling them to use [Redis](http://redis.io) in
their applications.


### Getting started ###

Supposing that the skeleton of your application is ready, you simply need to register this service
provider by specifying the parameters and options needed to access Redis:

```php
$app->register(new Predis\Silex\ClientServiceProvider(), [
    'predis.parameters' => 'tcp://127.0.0.1:6379',
    'predis.options'    => [
        'prefix'  => 'silex:',
        'profile' => '3.0',
    ],
]);
```

This will register one instance of `Predis\Client` accessible from anywhere in your application by
using `$app['predis']`. Both `predis.parameters` and `predis.options` are optional and they accept
the same values accepted by the constructor of `Predis\Client` (see the documentation of Predis).

Certain applications might need more than one client to reach different servers or configured with
different options. In such cases you must use `Predis\Silex\ClientsServiceProvider` providing a list
of clients with their own parameters and options using `predis.clients`:

```php
$app->register(new Predis\Silex\ClientsServiceProvider(), [
    'predis.clients' => [
        'first' => 'tcp://127.0.0.1:6379',
        'second' => [
            'host' => '127.0.0.1',
            'port' => 6380,
        ],
        'third' => [
            'parameters' => 'tcp://127.0.0.1:6381',
            'options' => [
                'profile' => 'dev',
                'prefix'  => 'silex:',
            ],
        ],
    ),
]);
```

Clients will be exposed to your application using `$app['predis'][$alias]` where `$alias` is the key
used to populate the items of `predis.clients`. Optionally, it is possible to set a default client
by specifying its alias in `predis.default_client` making it accessible simply by invoking methods
of `Predis\Client` directly against `$app['predis']`. Client instances are lazily initialized upon
the first access.

You can find more details on how to use this provider in the `examples` directory or the test suite.

### Reporting bugs and contributing code ###

Contributions are highly appreciated either in the form of pull requests for new features, bug fixes
or just bug reports. We only ask you to adhere to a [basic set of rules](CONTRIBUTING.md) before
submitting your changes or filing bugs on the issue tracker to make it easier for everyone to stay
consistent while working on the project.


### Project links ###

- [Source code](http://github.com/nrk/PredisServiceProvider)
- [Issue tracker](http://github.com/nrk/PredisServiceProvider/issues)


### Author ###

- [Daniele Alessandri](mailto:suppakilla@gmail.com) ([twitter](http://twitter.com/JoL1hAHN))


### Contributors ###

- Jérôme Macias ([github](http://github.com/jeromemacias))


### License ###

The code for Predis ServiceProvider is distributed under the terms of the [MIT license](LICENSE).
