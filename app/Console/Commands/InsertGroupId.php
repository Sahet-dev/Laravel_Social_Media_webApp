<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class InsertGroupId extends Command
{
    protected $signature = 'group_id:id';

    protected $description = 'Inserts 15 into group_id column of the posts table';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Logic to insert '15' into the 'group_id' column of the 'posts' table
        Post::query()->update(['group_id' => 15]);

        $this->info('Group ID inserted successfully.');
    }
}
