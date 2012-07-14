# PredisServiceProvider #

This service provider for the __[Silex](http://silex-project.org)__ microframework enables developers to easily
use __[Predis](http://github.com/nrk/predis)__ in their applications to connect to __[Redis](http://redis.io)__.


## Getting started ##

Using this service provider in your application is easy and requires the use of [Composer](http://packagist.org/about-composer)
to download and set up all the needed dependencies by adding `"predis/service-provider": "0.3.*@stable"` to the
list of `require`d libraries in your `composer.json` file.

After installing, and supposing that you already have the scheleton of your Silex application ready, you just need
to register the service provider with the parameters needed to access the Redis server instance and configure the
underlying Predis client:

``` php
<?php
/* ... */
$app->register(new Predis\Silex\PredisServiceProvider(), array(
    'predis.parameters' => 'tcp://127.0.0.1:6379/',
    'predis.options'    => array('profile' => '2.2'),
));
/* ... */
```

Both `predis.parameters` and `predis.options` are actually optional and accept the same values of the constructor
method of `Predis\Client`. It is also possible to define multiple clients identified by aliases with their own
parameters and options using `predis.clients`. Each client instance will be initialized lazily upon first access:

``` php
<?php
/* ... */
$app->register(new Predis\Silex\PredisServiceProvider(), array(
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
_examples_ directory included in the repository or the test suite in the _tests_ directory.


## Testing ##

In order to be able to run the test suite of the provider you must run `php composer.phar install`  in the root
of the repository to install the needed dependencies.

```bash
  $ wget http://getcomposer.org/composer.phar
  $ php composer.phar install
  $ phpunit
```


## Dependencies ##

- PHP >= 5.3.2
- Predis >= 0.7.0


## Project links ##
- [Source code](http://github.com/nrk/PredisServiceProvider)
- [Issue tracker](http://github.com/nrk/PredisServiceProvider/issues)


## Author ##

- [Daniele Alessandri](mailto:suppakilla@gmail.com) ([twitter](http://twitter.com/JoL1hAHN))


## Contributors ##

- Jérôme Macias ([github](http://github.com/jeromemacias))


## License ##

The code for PredisServiceProvider is distributed under the terms of the __MIT license__ (see LICENSE).
