<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class DeleteRecord extends Command
{
    protected $signature = 'delete:record {id}';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a record from the database by ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');

        // Find the record by ID
        $record = Post::find($id);

        // Check if the record exists
        if (!$record) {
            $this->error('Record not found.');
            return;
        }

        // Delete the record
        $record->forceDelete();

        $this->info('Record deleted successfully.');
    }
}
