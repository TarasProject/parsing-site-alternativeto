<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\Categories;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParsingCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $url;
    protected $parent_id;
    protected $page;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$parent_id,$page)
    {
        $this->url = $data;
        $this->parent_id = $parent_id;
        $this->page = $page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->saveAnalysis($this->url,$this->parent_id,$this->page);
    }

    protected  function saveAnalysis($url,$parent_id,$page)
    {

        $mas_html_sub_cat = $this->saveCurl($url);
        $url_exist = preg_replace('/\?p=\d+/', '', $url);
        if (!Categories::where('url', $url_exist)->first()) {
            $level = 2;
            preg_match_all("/<h1.+?>(.+?)<\/h1>/", $mas_html_sub_cat, $title);
            $title = $title[1][0];
            preg_match_all("/<meta\sname=\"description\"\scontent=\"([^<>]*?)\"\s*\/>/", $mas_html_sub_cat, $meta_description);
            if (isset($meta_description[1][0])) {
                $meta_description = $meta_description[1][0];
            } else {
                $meta_description = null;
            }

            var_dump("---SubCategiry---");
            var_dump($title);
            $res = Categories::updateOrCreate(
                [
                    'url' => $url,
                ],
                [
                    'title' => $title,
                    'level' => $level,
                    'meta_description' => $meta_description,
                    'parent_id' => $parent_id,
                ]
            );
//            $cat_id = $res->id;
//            var_dump($cat_id);
        }
        $categories_id = Categories::all()->last()->id;
        // на даній сторінці знаходяться також програми витягую їх url
        preg_match_all("/(<li\sdata-testid.*?<\/div><\/li>)/", $mas_html_sub_cat, $mas_app);
        foreach ($mas_app[0] as $app) {
            preg_match_all("/(<a href=\"\/software\/.*?<\/a>)/", $app, $link_app);
            preg_match_all("/<a href=\"(.*)\"\s.*>(.*)<\/a>/", $link_app[0][0], $title_app);
            $url_app = "https://alternativeto.net" . $title_app[1][0];

            dispatch(new ParsingApp($url_app,$categories_id,$page))->onQueue('analytics');



        }
        preg_match_all("/<div class=\".*pagination\"><span.*next\">(Next)<\/span><\/div>/",$mas_html_sub_cat,$pagination);

        if (!empty($pagination[1][0]) && $pagination[1][0] === "Next") {
            $page++;
            $url = preg_replace('/\?p=\d+/', '', $url);
            $url = $url."?p=".$page;
            dispatch(new ParsingCategory($url,$parent_id,$page))->onQueue('analytics');
        }
    }
    protected function saveCurl($url)
    {
        $agent ='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.96 Safari/537.36';
        $config = '/tmp/cookies.txt';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $config);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $config);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $mas_html = curl_exec($ch);
        curl_close($ch);
        return $mas_html;
    }
}
