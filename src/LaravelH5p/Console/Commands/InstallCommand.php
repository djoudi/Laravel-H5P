<?php

namespace Djoudi\LaravelH5p\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'h5p:install 
                            {--force : Overwrite existing files}
                            {--skip-migrations : Skip running migrations}';

    protected $description = 'Install the H5P package with all necessary files and configurations';

    public function handle(): int
    {
        $this->info(' ');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║       Laravel H5P Pro Installation       ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info(' ');

        // Step 1: Publish config
        $this->task('Publishing configuration files', function () {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'laravel-h5p-config',
                '--force' => $this->option('force'),
            ]);

            return true;
        });

        // Step 2: Publish migrations
        $this->task('Publishing migrations', function () {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'laravel-h5p-migrations',
                '--force' => $this->option('force'),
            ]);

            return true;
        });

        // Step 3: Publish assets
        $this->task('Publishing assets', function () {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'laravel-h5p-assets',
                '--force' => $this->option('force'),
            ]);

            return true;
        });

        // Step 4: Run migrations
        if (!$this->option('skip-migrations')) {
            $this->task('Running migrations', function () {
                $this->callSilently('migrate');

                return true;
            });
        }

        // Step 5: Create storage directories
        $this->task('Creating storage directories', function () {
            $directories = [
                storage_path('app/public/h5p'),
                storage_path('app/public/h5p/content'),
                storage_path('app/public/h5p/libraries'),
                storage_path('app/public/h5p/temp'),
            ];

            foreach ($directories as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }

            return true;
        });

        // Step 6: Create storage link
        $this->task('Creating storage link', function () {
            if (!file_exists(public_path('storage'))) {
                $this->callSilently('storage:link');
            }

            return true;
        });

        $this->newLine();
        $this->info('✅ H5P has been installed successfully!');
        $this->newLine();

        $this->table(['Next Steps'], [
            ['1. Configure your .env file with H5P settings'],
            ['2. Visit /h5p/library to manage H5P libraries'],
            ['3. Upload your first H5P content'],
        ]);

        $this->newLine();
        $this->comment('For LRS/xAPI support, add these to your .env:');
        $this->line('  LRS_ENABLED=true');
        $this->line('  LRS_ENDPOINT=https://your-lrs.com/data/xAPI');
        $this->line('  LRS_USERNAME=your-key');
        $this->line('  LRS_PASSWORD=your-secret');
        $this->newLine();

        return self::SUCCESS;
    }

    protected function task(string $description, callable $task): void
    {
        $this->output->write("  <comment>→</comment> {$description}... ");

        try {
            $result = $task();
            $this->output->writeln($result ? '<info>✓</info>' : '<error>✗</error>');
        } catch (\Exception $e) {
            $this->output->writeln('<error>✗</error>');
            $this->error('    Error: '.$e->getMessage());
        }
    }
}
