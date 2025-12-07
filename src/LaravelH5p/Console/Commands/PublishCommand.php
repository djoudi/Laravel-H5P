<?php

namespace Djoudi\LaravelH5p\Console\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'h5p:publish 
                            {--config : Publish only configuration}
                            {--assets : Publish only assets}
                            {--views : Publish only views}
                            {--force : Overwrite existing files}';

    protected $description = 'Publish H5P package files';

    public function handle(): int
    {
        $force = $this->option('force');
        $publishedSomething = false;

        if ($this->option('config') || (!$this->option('assets') && !$this->option('views'))) {
            $this->info('Publishing configuration...');
            $this->call('vendor:publish', [
                '--tag'   => 'laravel-h5p-config',
                '--force' => $force,
            ]);
            $publishedSomething = true;
        }

        if ($this->option('assets') || (!$this->option('config') && !$this->option('views'))) {
            $this->info('Publishing assets...');
            $this->call('vendor:publish', [
                '--tag'   => 'laravel-h5p-assets',
                '--force' => $force,
            ]);
            $publishedSomething = true;
        }

        if ($this->option('views')) {
            $this->info('Publishing views...');
            $this->call('vendor:publish', [
                '--tag'   => 'laravel-h5p-views',
                '--force' => $force,
            ]);
            $publishedSomething = true;
        }

        if ($publishedSomething) {
            $this->info('✅ Files published successfully!');
        }

        return self::SUCCESS;
    }
}
