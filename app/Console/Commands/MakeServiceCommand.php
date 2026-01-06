<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class MakeServiceCommand extends GeneratorCommand
{
    protected $name = 'make:service';
    protected $description = 'Create a new service interface, implementation class, and update CustomServiceProvider';
    protected $type = 'Service';

    protected function getStub()
    {
        return '';
    }

    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);
        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    public function handle()
    {
        $serviceName = $this->getNameInput();
        $namespace = $this->qualifyClass("Services\\{$serviceName}");

        // Generate Interface
        $interfaceName = $namespace . "\\{$serviceName}";
        $interfacePath = $this->getPath($interfaceName);

        if ($this->alreadyExists($interfaceName)) {
            $this->error('Service Interface already exists!');
            return false;
        }

        $this->makeDirectory($interfacePath);
        $this->files->put($interfacePath, $this->buildInterfaceClass($interfaceName));

        // Generate Implementation Class
        $implName = $namespace . "\\{$serviceName}Impl";
        $implPath = $this->getPath($implName);

        if ($this->alreadyExists($implName)) {
            $this->error('Service Implementation already exists!');
            return false;
        }

        $this->makeDirectory($implPath);
        $this->files->put($implPath, $this->buildImplClass($implName, $serviceName));

        // Handle CustomServiceProvider
        $providerName = $this->qualifyClass("Providers\\CustomServiceProvider");
        $providerPath = $this->getPath($providerName);

        if (!$this->files->exists($providerPath)) {
            // Create new CustomServiceProvider
            $this->makeDirectory($providerPath);
            $this->files->put($providerPath, $this->buildProviderClass($providerName, $serviceName));
            $this->info('CustomServiceProvider created successfully.');
        } else {
            // Update existing CustomServiceProvider
            $this->updateProviderClass($providerPath, $serviceName);
            $this->info('CustomServiceProvider updated successfully.');
        }

        // Register CustomServiceProvider in config/app.php
        $this->registerServiceProvider();

        $this->info($this->type . ' created and CustomServiceProvider configured successfully.');
    }

    protected function buildInterfaceClass($name)
    {
        $stub = $this->files->get($this->getStubPath('interface'));
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function buildImplClass($name, $interfaceName)
    {
        $stub = $this->files->get($this->getStubPath('impl'));
        $interfaceClass = $interfaceName;
        $stub = str_replace('{{ interfaceClass }}', $interfaceClass, $stub);
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function buildProviderClass($name, $serviceName)
    {
        $stub = $this->files->get($this->getStubPath('provider'));
        $stub = str_replace('{{ serviceName }}', $serviceName, $stub);
        $stub = str_replace('{{ serviceNamespace }}', "App\\Services\\{$serviceName}", $stub);
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function updateProviderClass($providerPath, $serviceName)
    {
        $content = $this->files->get($providerPath);
        $binding = "\$this->app->bind(\\App\\Services\\{$serviceName}\\{$serviceName}::class, \\App\\Services\\{$serviceName}\\{$serviceName}Impl::class);";

        // Check if binding already exists
        if (Str::contains($content, $binding)) {
            return;
        }

        // Find the register method and append the binding
        $search = 'public function register()' . PHP_EOL . '    {' . PHP_EOL;
        $replace = 'public function register()' . PHP_EOL . '    {' . PHP_EOL . '        ' . $binding . PHP_EOL;

        if (Str::contains($content, $search)) {
            $newContent = str_replace($search, $replace, $content);
            $this->files->put($providerPath, $newContent);
        } else {
            // If register method not found, append binding at the end of register method
            $search = 'public function register()' . PHP_EOL . '    {';
            $replace = 'public function register()' . PHP_EOL . '    {' . PHP_EOL . '        ' . $binding . PHP_EOL;
            $newContent = str_replace($search, $replace, $content);
            $this->files->put($providerPath, $newContent);
        }
    }

    protected function getStubPath($type)
    {
        return resource_path('stubs/service.' . $type . '.stub');
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the service'],
        ];
    }

    protected function replaceClass($stub, $name)
    {
        $class = class_basename($name);
        return str_replace('{{ class }}', $class, $stub);
    }

    protected function registerServiceProvider()
    {
        $configPath = config_path('app.php');
        $configContent = $this->files->get($configPath);

        $providerClass = "App\\Providers\\CustomServiceProvider::class,";

        // Check if provider is already registered
        if (Str::contains($configContent, $providerClass)) {
            return;
        }

        // Find the providers array in config/app.php
        $search = "'providers' => [";
        $replace = "'providers' => [\n        {$providerClass}";

        if (Str::contains($configContent, $search)) {
            $newContent = str_replace($search, $replace, $configContent);
            $this->files->put($configPath, $newContent);
            $this->info("CustomServiceProvider registered in config/app.php.");
        } else {
            $this->warn("Could not find 'providers' array in config/app.php. Please register {$providerClass} manually.");
        }
    }
}
