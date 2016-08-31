<?php

namespace Flipbox\ApiManager\Commands;

use Illuminate\Support\Str;
use Flipbox\ApiManager\ApiManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\RuntimeException;

class makeCommand extends GeneratorCommand
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'api:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make api resource';

    /**
     * api managre
     *
     * @var ApiManager
     */
    protected $apiManager;

    /**
     * Create a new MakeCommand instance.
     *
     * @param param type 
     * @return void
     */
    public function __construct(ApiManager $apiManager, Filesystem $files)
    {
        parent::__construct($files);

        $this->apiManager = $apiManager;
    }
    
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->getTypeInput() === 'request') {
            return __DIR__.'/../Stub/request.stub';
        }

        if ($this->option('resource')) {
            return __DIR__.'/../Stub/controller.stub';
        }

        return __DIR__.'/../Stub/controller.plain.stub';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        if (! $this->apiManager->isVersionExists($this->argument('version'))) {
            $this->error('version is not exists');
            return false;
        }

        parent::fire();
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->apiManager->getNamespace($this->argument('version'), ucwords(Str::plural($this->getTypeInput())));
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getTypeInput()
    {
        return strtolower(trim($this->argument('type')));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['type', InputArgument::REQUIRED, 'Resource type: Controller or Request', null],
            ['name', InputArgument::REQUIRED, 'Class name of resource', null],
            ['version', InputArgument::REQUIRED, 'Version to place resource', null],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['resource', null, InputOption::VALUE_NONE, 'Generate a resource controller class.', null],
        ];
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $namespace = $this->getNamespace($name);
        $type = ucwords($this->argument('type'));

        return str_replace("use $namespace\Api$type;\n", '', parent::buildClass($name));
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            'DummyNamespace', $this->getNamespace($name), $stub
        );

        $stub = str_replace(
            'DummyRootNamespace', $this->getVersionNamespace($this->argument('version')), $stub
        );

        return $this;
    }

    /**
     * get version namespace
     *
     * @param string $version
     * @return string
     */
    protected function getVersionNamespace($version)
    {
        return rtrim($this->apiManager->getNamespace($this->argument('version'), ''), '\\');
    }
}
