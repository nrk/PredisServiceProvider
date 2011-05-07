# PredisExtension #

This extension for the __[Silex](http://silex-project.org)__ microframework enables developers to easily use
__[Predis](http://github.com/nrk/predis)__ in their applications to connect to __[Redis](http://redis.io)__.


## Getting started ##

Starting to use this extension is quite easy. Supposing that you already have the scheleton of your Silex
application ready, you just need to register the __Predis\Silex__ namespace to point to the path of where
the source code of the extension has been placed and then add an instance to the application object:

``` php
<?php
/* ... */
$app['autoloader']->registerNamespaces(array(
    'Predis\Silex' => __DIR__.'/../vendor/PredisExtension/lib',
));

$app->register(new Predis\Silex\PredisExtension(), array(
    'predis.class_path' => __DIR__.'/../vendor/Predis/lib',
    'predis.parameters' => 'tcp://127.0.0.1:6379/',
    'predis.options'    => array('profile' => '2.2'),
));
/* ... */
```

The __predis.class_path__ option lets you specify where to look for Predis. Both __predis.parameters__ and
__predis.options__ are optional and they accept the same values of the constructor method of Predis\Client.

If you are looking for simple but complete examples of how to use this extension you can have a look at the
_examples_ directory that is included in the repository.


## Dependencies ##

- PHP >= 5.3.2
- Predis >= 0.7.0


## Project links ##
- [Source code](http://github.com/nrk/PredisExtension)
- [Issue tracker](http://github.com/nrk/PredisExtension/issues)


## Author ##

- [Daniele Alessandri](mailto:suppakilla@gmail.com) ([twitter](http://twitter.com/JoL1hAHN))


## License ##

The code for PredisExtension is distributed under the terms of the __MIT license__ (see LICENSE).
