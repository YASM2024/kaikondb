<?php

namespace Kaikon2\Kaikondb\Jobs;

use Kaikon2\Kaikondb\Models\Article;

// use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateArticleTitlesAndAuthors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Article::where(function ($query) {
            $query->whereNull('title_en')
                  ->orWhere('title_en', '');
        })->update(['title_en' => \DB::raw('title')]);
    
        Article::where(function ($query) {
            $query->whereNull('author_en')
                  ->orWhere('author_en', '');
        })->update(['author_en' => \DB::raw('author')]);
    }
    
}
