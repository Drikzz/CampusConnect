<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tag;
use Illuminate\Support\Str;

class FixTagNames extends Command
{
    protected $signature = 'tags:fix-names';
    protected $description = 'Fix tag names that are appearing as numbers';

    public function handle()
    {
        $tags = Tag::all();
        
        $tagNames = [
            'Books',
            'Electronics',
            'School Supplies',
            'Notes',
            'Uniforms',
            'Lab Equipment',
            'Sports',
            'Art Supplies',
            'Computing',
            'Stationery'
        ];
        
        $this->info('Fixing tag names...');
        
        // Delete existing tags if they're corrupted
        if ($tags->count() > 0 && !is_string($tags->first()->name)) {
            $this->info('Deleting corrupted tags...');
            Tag::truncate();
            
            // Create new tags with correct data
            foreach ($tagNames as $name) {
                Tag::create([
                    'name' => $name,
                    'slug' => Str::slug($name)
                ]);
            }
            
            $this->info('Tags recreated successfully.');
        } else {
            $this->info('No corrupted tags found.');
        }
        
        return Command::SUCCESS;
    }
}
