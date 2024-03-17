<?php

namespace App\Console\Commands;

use App\Models\GroupUser;
use Illuminate\Console\Command;

class Approved extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:approved';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates status column record to approved on GroupUser table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating records...');

        // Your update logic here
        $updatedCount = GroupUser::where('status', '=', 'pending')->update(['status' => 'approved']);

        $this->info("Updated {$updatedCount} records.");

        $this->info('Records updated successfully.');
    }
}
