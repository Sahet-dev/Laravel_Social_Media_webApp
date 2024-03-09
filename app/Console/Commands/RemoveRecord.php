<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GroupUser;

class RemoveRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:record {column} {id}';
    protected $description = 'Remove a record from the database by column and ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $column = $this->argument('column');
        $id = $this->argument('id');

        // Find the record by column and ID
        $record = GroupUser::where($column, $id)->first();

        // Check if the record exists
        if (!$record) {
            $this->error('Record not found.');
            return;
        }

        // Delete the record
        $record->delete();

        $this->info('Record deleted successfully.');
    }
}
