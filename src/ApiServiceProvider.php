<?php 

namespace Flipbox\ApiManager;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
	/**
	 * register commands
	 *
	 * @var array
	 */
	protected $commands = [
		Commands\NewCommand::class,
		Commands\MakeCommand::class
	];

	/**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    	$configPath = __DIR__ . '/Config/api-manager.php';
        $this->mergeConfigFrom($configPath, 'api-manager');
		
		$this->app->singleton('api-manager', function($app){
			return new ApiManager($app);
		});

        $this->app->alias('api-manager', 'Flipbox\ApiManager\ApiManager');

		$this->commands($this->commands);
    }

	/**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
		$this->publishes([
            __DIR__.'/Config/api-manager.php' => config_path('api-manager.php'),
        ], 'config');

        $apiManager = $this->app['api-manager'];
        $apiManager->boot();
	}
}
