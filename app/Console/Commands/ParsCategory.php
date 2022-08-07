<?php

namespace App\Console\Commands;

use App\Jobs\ParsingCategory;
use Illuminate\Console\Command;

class ParsCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:category {--url=} {--csvfile=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'parse category';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
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
        dispatch(new ParsingCategory($url))->onQueue('analytics');

    }
}
