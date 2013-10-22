<?php namespace SeeClickFix\API;

use Illuminate\Support\ServiceProvider;
use SeeClickFixSDK;

class APIServiceProvider extends ServiceProvider {

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
			return new API($app["config"], $app["session.store"]);
	    });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}