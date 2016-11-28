v2.0.0 (2016-11-28)
================================================================================

- Updated code to work with Pimple 3.0 and Silex 2.0.


v1.0.0 (2014-08-06)
================================================================================

- Bumped required version of Predis to ~1.0.

- All of the service provider classes have been renamed.

- Switched to PSR-4 for autoloading.


v0.4.2 (2013-10-18)
================================================================================

- Fix Silex dependency to allow stable and dev versions.


v0.4.1 (2013-03-19)
================================================================================

- Added `predis.client_constructor` to easily override the actual instance of
  `Predis\ClientInterface` returned by the provider when creating a new client
  object.

- Minimum required version of Predis is now v0.8.3.


v0.4.0 (2013-02-03)
================================================================================

- The service provider now requires Predis v0.8.

- Now `Predis\Silex\PredisServiceProvider` is used only to provide single-client
  configurations while the new `Predis\Silex\MultiPredisServiceProvider` can be
  used to configure multiple clients. This is a breaking change from previous
  versions in which both configurations were handled by the sameprovider class.

- Due to the nature of the above mentioned change, it is no more possible to
  define a default client when using a multiple-clients configuration.


v0.3.0 (2012-07-14)
================================================================================

- Fixed service provider configuration after the recent addition of the `boot()`
  method in Silex.

- Removed the `class_path` option, Silex removed its autoloader service and now
  relies on Composer.


v0.2.4 (2012-06-01)
================================================================================

- Implemented a empty Silex\ServiceProviderInterface::boot() needed after recent
  changes in Silex.


v0.2.3 (2011-12-11)
================================================================================

- There are no actual changes in the code of this release, aside from a bump in
  the required version of Predis in the composer.json file to target the tagged
  release of v0.7.0,


v0.2.2 (2011-11-13)
================================================================================

- Added the ability to specify multiple clients using 'predis.clients' with an
  array of connection parameters and client options for each client.


v0.2.1 (2011-10-02)
================================================================================

- Added support for Composer to manage installation and dependencies.


v0.2.0 (2011-10-02)
================================================================================

- Renamed PredisExtension to PredisServiceProvider after internal changes in the
  naming of some classes and interfaces of Silex.


v0.1.0 (2011-05-07)
================================================================================

- Initial release.
