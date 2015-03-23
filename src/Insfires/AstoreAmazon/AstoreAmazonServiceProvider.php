<?php namespace Insfires\AstoreAmazon;

use Illuminate\Support\ServiceProvider;

class AstoreAmazonServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
     *
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->package('insfires/astore-amazon');
        $this->app['astore'] = $this->app->share(function($app)
        {
            return new AStore();
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
