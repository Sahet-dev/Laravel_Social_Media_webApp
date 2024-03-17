<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GroupUser;

class Pending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates status column record to pending on GroupUser table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating records...');

        // Your update logic here
        $updatedCount = GroupUser::where('status', '=', 'approved')->update(['status' => 'pending']);

        $this->info("Updated {$updatedCount} records.");

        $this->info('Records updated successfully.');
    }
}
