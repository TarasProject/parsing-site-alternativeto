<?php

namespace App\Console\Commands;


use App\Jobs\ParsingCategories;
//use App\Models\Pars;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\NullableType;
use Sunra\PhpSimple\HtmlDomParser;
use function simplehtmldom_1_5\str_get_html;

class ParsContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:content {--url=} {--csvfile=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        dispatch(new ProcessParsing($url))->onQueue('analytics');

    }

}
