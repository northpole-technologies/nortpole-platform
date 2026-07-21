<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'northpole:make-module {name}';

    protected $description = 'Create a new Northpole platform module';

    public function handle(): int
    {
        $moduleName = Str::studly($this->argument('name'));
        $modulePath = base_path("modules/{$moduleName}");

        if (File::exists($modulePath)) {
            $this->error("Module {$moduleName} already exists.");

            return self::FAILURE;
        }

        $folders = [
            'src/Providers',
            'src/Http/Controllers',
            'src/Models',
            'src/Services',
            'src/Contracts',
            'src/Events',
            'src/Listeners',
            'src/Jobs',
            'src/Console',
            'src/Support',
            'config',
            'routes',
            'resources/views',
            'resources/lang',
            'resources/assets/css',
            'resources/assets/js',
            'resources/assets/images',
            'database/migrations',
            'database/seeders',
            'database/factories',
            'tests',
            'docs',
        ];

        foreach ($folders as $folder) {
            File::makeDirectory(
                "{$modulePath}/{$folder}",
                0755,
                true,
                true
            );
        }

        $slug = Str::kebab($moduleName);

        $moduleJson = [
            'name' => Str::headline($moduleName),
            'slug' => $slug,
            'version' => '1.0.0',
            'description' => '',
            'provider' => "Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider",
        ];

        File::put(
            "{$modulePath}/module.json",
            json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        File::put(
            "{$modulePath}/README.md",
            "# " . Str::headline($moduleName) . PHP_EOL . PHP_EOL .
            "Northpole Platform Module" . PHP_EOL
        );

        $providerCode = <<<PHP
<?php

namespace Modules\\{$moduleName}\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$moduleName}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
PHP;

        File::put(
            "{$modulePath}/src/Providers/{$moduleName}ServiceProvider.php",
            $providerCode
        );

        $this->newLine();
        $this->info("Northpole module {$moduleName} created successfully.");
        $this->line("Location: modules/{$moduleName}");

        return self::SUCCESS;
    }
}