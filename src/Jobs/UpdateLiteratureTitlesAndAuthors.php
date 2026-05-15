<?php

namespace Kaikon2\Kaikondb\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Kaikon2\Kaikondb\Models\Literature;

class UpdateLiteratureTitlesAndAuthors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Literature::where(function ($query) {
            $query->whereNull('title_en')
                ->orWhere('title_en', '');
        })->update(['title_en' => DB::raw('title')]);

        Literature::where(function ($query) {
            $query->whereNull('author_en')
                ->orWhere('author_en', '');
        })->update(['author_en' => DB::raw('author')]);
    }
}
