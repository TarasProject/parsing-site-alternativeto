<?php

namespace App\Console\Commands;

use App\Jobs\ParsingCategories;
use App\Models\Categories;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParsCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:categories {--url=} {--csvfile=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'parse categories';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function filter($string){
        return html_entity_decode($string, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $url = $this->option('url');
        if(!preg_match('/alternativeto\.net/', $url)){
             return 'invalid url'. "\n";
        }
        dispatch(new ParsingCategories($url))->onQueue('analytics');

    }
}
