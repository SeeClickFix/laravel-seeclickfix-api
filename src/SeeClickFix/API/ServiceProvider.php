<?php namespace SeeClickFix\API;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Auth\Guard;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('seeclickfix/laravel-seeclickfix-api');

        // Add 'SeeClickFix' facade alias
        AliasLoader::getInstance()->alias('SeeClickFix', 'SeeClickFix\API\Facade');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	    // Register 'SeeClickFix'
	    $this->app["SeeClickFix"] = $this->app->share(function($app)
	    {
			return new Manager($app["config"], $app["session.store"]);
	    });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('seeclickfix');
	}
}