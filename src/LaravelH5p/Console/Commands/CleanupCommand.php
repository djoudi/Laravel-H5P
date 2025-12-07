<?php

namespace Djoudi\LaravelH5p\Console\Commands;

use Djoudi\LaravelH5p\Eloquents\H5pLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupCommand extends Command
{
    protected $signature = 'h5p:cleanup 
                            {--unused : Remove unused libraries}
                            {--temp : Clean temporary files}
                            {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Clean up H5P files and unused libraries';

    public function handle(): int
    {
        $this->info('H5P Cleanup');
        $this->newLine();

        $dryRun = $this->option('dry-run');

        if ($this->option('temp') || !$this->option('unused')) {
            $this->cleanTempFiles($dryRun);
        }

        if ($this->option('unused')) {
            $this->cleanUnusedLibraries($dryRun);
        }

        if ($dryRun) {
            $this->warn('Dry run mode - no files were deleted.');
        }

        return self::SUCCESS;
    }

    protected function cleanTempFiles(bool $dryRun): void
    {
        $tempPath = storage_path('app/public/h5p/temp');

        if (!is_dir($tempPath)) {
            $this->info('No temporary files found.');

            return;
        }

        $files = File::allFiles($tempPath);
        $count = count($files);

        if ($count === 0) {
            $this->info('No temporary files found.');

            return;
        }

        $this->info("Found {$count} temporary file(s)");

        if (!$dryRun) {
            File::cleanDirectory($tempPath);
            $this->info("✅ Cleaned {$count} temporary file(s)");
        }
    }

    protected function cleanUnusedLibraries(bool $dryRun): void
    {
        $this->info('Checking for unused libraries...');

        // Get libraries with no content dependencies
        $unusedLibraries = H5pLibrary::whereDoesntHave('contents')
            ->where('runnable', 0)
            ->get();

        $count = $unusedLibraries->count();

        if ($count === 0) {
            $this->info('No unused libraries found.');

            return;
        }

        $this->table(
            ['ID', 'Name', 'Version'],
            $unusedLibraries->map(fn ($lib) => [
                $lib->id,
                $lib->name,
                "{$lib->major_version}.{$lib->minor_version}.{$lib->patch_version}",
            ])
        );

        if (!$dryRun) {
            if ($this->confirm("Delete {$count} unused library/libraries?")) {
                foreach ($unusedLibraries as $library) {
                    $library->delete();
                }
                $this->info("✅ Deleted {$count} unused library/libraries");
            }
        }
    }
}
