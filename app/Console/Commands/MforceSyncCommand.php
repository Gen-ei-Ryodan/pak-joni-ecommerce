<?php

namespace App\Console\Commands;

use App\Services\MforceSyncService;
use Illuminate\Console\Command;

class MforceSyncCommand extends Command
{
    protected $signature = 'mforce:sync
                            {--brand= : Sync only a specific brand slug}
                            {--dry-run : Simulate sync without saving to database}';

    protected $description = 'Sync products from MForce API to local database';

    public function handle(MforceSyncService $service): int
    {
        $brand = $this->option('brand');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be saved.');
        }

        $this->info('Starting MForce sync...');
        $start = microtime(true);

        if ($brand) {
            $this->info("Syncing brand: {$brand}");
            $stats = $service->syncBrand($brand, 'cli', $dryRun);
        } else {
            $this->info('Syncing all brands...');
            $stats = $service->syncAll('cli', $dryRun);
        }

        $elapsed = round(microtime(true) - $start, 2);

        $this->newLine();
        $this->table(['Status', 'Count'], [
            ['<fg=green>Created</>', $stats['created']],
            ['<fg=yellow>Updated</>', $stats['updated']],
            ['<fg=blue>Skipped</>', $stats['skipped']],
            ['<fg=red>Archived</>', $stats['archived']],
            ['<fg=red>Errors</>', $stats['errors']],
        ]);

        $this->info("Sync complete in {$elapsed}s.");

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
