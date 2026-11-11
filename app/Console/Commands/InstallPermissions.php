<?php

namespace App\Console\Commands;

use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:install-permissions')]
#[Description('Sync roles and permissions without resetting the database')]
class InstallPermissions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing roles and permissions...');
        app(PermissionSeeder::class)->run();

        $this->info('Roles and permissions synchronized successfully.');
        $this->info('Installation completed successfully.');

        return self::SUCCESS;
    }
}
