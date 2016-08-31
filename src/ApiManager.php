<?php 

namespace Flipbox\ApiManager;

use Illuminate\Filesystem\Filesystem;
use Flipbox\ApiManager\Exceptions\ApiVersionException;

class ApiManager
{
	/**
     * The Laravel application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * api manager configuration
     *
     * @var array
     */
    protected $config;

    /**
     * laravel filesystem
     *
     * @var Filesystem
     */
    protected $files;

	/**
	 * Create a new ApiManager instance.
	 *
	 * @param Application $app
	 * @return void
	 */
	public function __construct($app = null)
	{
		if (!$app) {
            $app = app();
        }

        $this->app = $app;
        $this->config = $this->app['config']['api-manager'];
        $this->files = new Filesystem;
	}

	/**
	 * boot api Manager
	 *
	 * @return void
	 */
	public function boot()
	{
		if (! $this->config['enabled']) {
			return;
		}

		$versions = $this->getVersionList();

		if (count($versions) === 0) return;

		foreach ($versions as $version) {
			$this->registerApiVersion($version);
		}
		
		$this->registerApiBaseUrl();
	}

	/**
	 * register global api
	 *
	 * @return void
	 */
	protected function registerApiBaseUrl()
	{
        $version = $this->config['default_version'];

        if (! empty($version) AND $this->isVersionExists($version)) {
            $this->registerApiVersion($version, true);
        }        
	}

    /**
     * register api version
     *
     * @param string $name
     * @param bool $asRoot
     * @return void
     */
    protected function registerApiVersion($name, $asRoot = false)
    {
    	$versionsConfig = $this->config['versions'];

    	if (is_array($versionsConfig) AND count($versionsConfig) > 0 AND array_key_exists($name, $versionsConfig)) {
    		if (isset($versionsConfig[$name]['enabled']) AND ! $versionsConfig[$name]['enabled']) {
    			return;
    		}
    	}

        $routes = [
            'middleware' => $this->getMiddleware($name),
            'namespace' => $this->getNamespace($name),
            'prefix' => $this->getPrefix($name, $asRoot),
        ];

        $versionPath = $this->getVersionPath($name);
        
        $this->app['router']->group($routes, function() use ($versionPath) {
            $this->includeRouteFile($versionPath);
        });
    }

    /**
     * get version list
     *
     * @return array
     */
    public function getVersionList()
    {
		$apiPath = $this->getNamespacePath();
		$versions = $this->files->directories($apiPath);

		foreach ($versions as &$version) {
			$version = $this->getVersionName($version);
		}

		return $versions;
    }

    /**
     * get api version name by path
     *
     * @param string $versionPath
     * @return string
     */
    protected function getVersionName($versionPath)
    {
    	return last(explode('/', $versionPath));
    }

    /**
     * get api version path by name
     *
     * @param string $name
     * @return string
     */
    public function getVersionPath($name)
    {
    	return $this->getNamespacePath().'/'.$name;
    }

	/**
     * get base api namespace
     *
     * @return string
     */
    public function getNamespacePath()
    {
        $namespaces = explode('\\', $this->config['namespace']);
        
        $baseDir = base_path(implode('/', array_map(function(&$val){
            if ($val === 'App') return 'app';
            return $val;
        }, $namespaces)));

        if (! $this->files->exists($baseDir)) {
            $this->files->makeDirectory($baseDir);
        }

        return $baseDir;
    }

    /**
     * include route file of version
     *
     * @param string $versionPath
     * @return void
     */
    protected function includeRouteFile($versionPath)
    {
    	if ($this->files->exists($versionPath.'/routes.php')) {
    		require $versionPath.'/routes.php';
    		return;
    	}

    	throw new ApiVersionException("Can not found api version routes file");
    }

    /**
     * get api middleware
     *
     * @param string $name
     * @return array
     */
    protected function getMiddleware($name)
    {
    	$globalMiddleware = is_array($this->config['middleware'])
    						? $this->config['middleware']
    						: [$this->config['middleware']];

    	$versionConfigMiddleware = isset($this->config['versions'][$name]['middleware'])
    					? $this->config['versions'][$name]['middleware']
    					: [];

    	$versionMiddleware = count($versionConfigMiddleware) > 0
    						? (is_array($versionConfigMiddleware) ? $versionConfigMiddleware : [$versionConfigMiddleware])
    						: [] ;

    	return array_merge($globalMiddleware, $versionMiddleware);
    }

    /**
     * get api prefix
     *
     * @param string $name
     * @param bool $asRoot
     * @return array
     */
    protected function getPrefix($name, $asRoot=false)
    {
    	$globalPrefix = explode('/', $this->config['prefix']);
    	$versionPrefix = [];

    	if (! $asRoot) {
	    	$versionConfigPrefix = isset($this->config['versions'][$name]['prefix'])
					? $this->config['versions'][$name]['prefix']
					: [];

	    	if (count($versionConfigPrefix) > 0) {
	    		$versionPrefix = explode('/', $versionConfigPrefix);
	    	} else {
	    		$versionPrefix = [$name];
	    	}
    	}

    	return implode('/', array_merge($globalPrefix, $versionPrefix));
    }

    /**
     * get api Namespace
     *
     * @param string $version
     * @return array
     */
    public function getNamespace($version, $type='Controllers')
    {
        return $this->config['namespace'] . '\\' . $version .'\\'. ucwords($type);
	}

	/**
	 * check is version exists
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isVersionExists($name)
	{
		$versionsList = $this->getVersionList();

		return in_array($name, $versionsList);
	}
}
