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

        // Detect line ending used in the file
        $eol = Str::contains($content, "\r\n") ? "\r\n" : "\n";

        $interfaceClass = "{$serviceName}";
        $implClass = "{$serviceName}Impl";
        $interfaceNamespace = "App\\Services\\{$serviceName}\\{$interfaceClass}";
        $implNamespace = "App\\Services\\{$serviceName}\\{$implClass}";

        $useInterface = "use {$interfaceNamespace};";
        $useImpl = "use {$implNamespace};";
        $binding = "\$this->app->bind({$interfaceClass}::class, {$implClass}::class);";

        // Check if binding already exists (check both short and FQCN format)
        if (Str::contains($content, $binding) || Str::contains($content, "{$serviceName}::class, \\App\\Services")) {
            return;
        }

        // Add use statements after the last existing use statement
        if (!Str::contains($content, $useInterface)) {
            // Find all use statements and get the last one
            // Pattern handles both \r\n and \n line endings
            $pattern = '/^use\s+[^;]+;\s*$/m';
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                $lastMatch = end($matches[0]);
                $lastUseStatement = $lastMatch[0];
                $lastUsePosition = $lastMatch[1];
                $lastUseEnd = $lastUsePosition + strlen($lastUseStatement);

                // Insert new use statements after the last one
                $newUseStatements = $eol . $useInterface . $eol . $useImpl;
                $content = substr($content, 0, $lastUseEnd) . $newUseStatements . substr($content, $lastUseEnd);
            }
        }

        // Find the register method and append the binding
        // Use regex to handle various formats
        $pattern = '/(public\s+function\s+register\s*\(\s*\)[\s\r\n]*\{[\s\r\n]*)/';
        if (preg_match($pattern, $content, $matches)) {
            $original = $matches[1];
            $replacement = $original . '        ' . $binding . $eol;
            $content = str_replace($original, $replacement, $content);
        }

        $this->files->put($providerPath, $content);
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
        $providersPath = base_path('bootstrap/providers.php');

        // Check if bootstrap/providers.php exists (Laravel 11+)
        if (!$this->files->exists($providersPath)) {
            $this->warn("Could not find bootstrap/providers.php. Please register App\\Providers\\CustomServiceProvider::class manually.");
            return;
        }

        $content = $this->files->get($providersPath);
        $providerClass = "App\\Providers\\CustomServiceProvider::class";

        // Check if provider is already registered
        if (Str::contains($content, $providerClass)) {
            return;
        }

        // Find the return array and add provider before the closing bracket
        // Pattern: matches the return array structure
        $pattern = '/return\s*\[\s*([\s\S]*?)\s*\];/';

        if (preg_match($pattern, $content, $matches)) {
            $existingProviders = trim($matches[1]);

            // Build new providers list
            if (!empty($existingProviders)) {
                // Ensure existing providers end with comma
                $existingProviders = rtrim($existingProviders, ',') . ',';
                $newProviders = $existingProviders . "\n    " . $providerClass . ",";
            } else {
                $newProviders = $providerClass . ",";
            }

            $newContent = preg_replace(
                $pattern,
                "return [\n    " . $newProviders . "\n];",
                $content
            );

            $this->files->put($providersPath, $newContent);
            $this->info("CustomServiceProvider registered in bootstrap/providers.php.");
        } else {
            $this->warn("Could not parse bootstrap/providers.php. Please register App\\Providers\\CustomServiceProvider::class manually.");
        }
    }
}
