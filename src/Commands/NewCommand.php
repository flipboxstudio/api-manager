<?php

namespace Flipbox\ApiManager\Commands;

use Exception;
use Illuminate\Console\Command;
use Flipbox\ApiManager\ApiManager;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Flipbox\ApiManager\Exceptions\ApiVersionException;

class NewCommand extends Command
{
    /**
     * file
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'api:new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new api folder structure';

    /**
     * api managre
     *
     * @var ApiManager
     */
    protected $apiManager;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files, ApiManager $apiManager)
    {
        parent::__construct();

        $this->files = $files;
        $this->apiManager = $apiManager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {   
        if ($this->apiManager->isVersionExists($this->argument('version'))) {
            $this->error('Api Version is already exists!');

            return false;
        }

        $path = $this->apiManager->getNamespacePath();
        $version = $this->makeNewVersion($path, $this->argument('version'));

        $this->buildApiResource($path, $version);
 
        $this->info("Success create new Api Version {$version}");
    }

    /**
     * build api reource
     *
     * @param string $path
     * @return void
     */
    protected function buildApiResource($path, $version)
    {
        $controllersPath = $path.'/'.$version.'/Controllers';
        $this->files->makeDirectory($controllersPath);
        $this->files->put($controllersPath.'/ApiController.php', $this->buildController($version));

        $requestPath = $path.'/'.$version.'/Requests';
        $this->files->makeDirectory($requestPath);
        $this->files->put($requestPath.'/ApiRequest.php', $this->buildRequest($version));

        $this->files->put($path.'/'.$version.'/routes.php', $this->buildRoute($version));
   }

    /**
     * create new api folder
     *
     * @param string $path
     * @param string $version
     * @return string
     */
    protected function makeNewVersion($path, $version)
    {
        $name = $version;

        if ($version === 'NEXT') {
            $name = $this->generateVersionName($path);
        }

        $this->makeVersionDirectory($path, $name);
        
        return $name;
    }

    /**
     * make version folder in specify path
     *
     * @param string $path
     * @param string $name
     * @return string
     */
    protected function makeVersionDirectory($path, $name)
    {
        try {        
            $this->files->makeDirectory($path.'/'.$name);
        } catch (Exception $e) {
            throw new ApiVersionException("Can not create Api Folder : {$e->getMessage()}");
        }

        return $path.'/'.$name;
    }

    /**
     * generate name of api version
     *
     * @param string $path
     * @return string
     */
    protected function generateVersionName($path, $last=null)
    {
        $version = 'v1';
        $directories = $this->files->directories($path);

        if (! is_null($last)) {
            $vNumber = $this->getVersionNumber($last);
            $vNumber += 1;

            return 'v'.$vNumber;
        }

        if (is_array($directories) AND count($directories) > 0) {
            $skip = 1;

            foreach ($directories as $directory) {
                $name = last(explode('/', $directory));

                if ($this->isValidVersion($name)) {
                    $skip++;
                }
            }

            $version =  'v'.$skip;
        }

        if ($this->files->exists($path.'/'.$version)) {
            return $this->generateVersionName($path, $version);
        }

        return $version;
    }

    /**
     * get version number
     *
     * @param string $name
     * @return mixed
     */
    protected function getVersionNumber($name)
    {
        if ($this->isValidVersion($name)) {
            $names = explode('v', $name);

            return $names[1];
        }

        return null;
    }

    /**
     * check name is valid directory in specify path
     *
     * @param string $name
     * @return bool
     */
    protected function isValidVersion($name)
    {
        $names = explode('v', $name);

        return count($names) === 2 AND $names[0] === '' AND is_numeric($names[1]);
    }

    /**
     * build route file
     *
     * @param string $version
     * @return string
     */
    protected function buildRoute($version)
    {
        $stub = $this->files->get(__DIR__.'/../Stub/routes.stub');

        return $this->replaceVersion($stub, $version);
    }

    /**
     * Replace the version for the given stub.
     *
     * @param  string  $stub
     * @param  string  $version
     * @return $this
     */
    protected function replaceVersion(&$stub, $version)
    {
        return str_replace('ApiVersion', $version, $stub);
    }

    /**
     * build route file
     *
     * @param string $version
     * @return string
     */
    protected function buildController($version)
    {
        $stub = $this->files->get(__DIR__.'/../Stub/controller.parent.stub');

        return $this->replaceNamespace($stub, $version, 'Controllers')->replaceClass($stub, 'ApiController');
    }

    /**
     * build route file
     *
     * @param string $version
     * @return string
     */
    protected function buildRequest($version)
    {
        $stub = $this->files->get(__DIR__.'/../Stub/request.parent.stub');

        return $this->replaceNamespace($stub, $version, 'Requests')->replaceClass($stub, 'ApiRequest');
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $version
     * @return $this
     */
    protected function replaceNamespace(&$stub, $version, $type)
    {
        $stub = str_replace(
            'DummyNamespace', $this->apiManager->getNamespace($version, $type), $stub
        );

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        return str_replace('DummyClass', $name, $stub);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['version', InputArgument::OPTIONAL, 'New api version', 'NEXT'],
        ];
    }
}
