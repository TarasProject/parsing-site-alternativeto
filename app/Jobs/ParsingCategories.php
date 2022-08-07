<?php

namespace App\Jobs;


use App\Models\Categories;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParsingCategories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->url = $data;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->saveAnalysis($this->url);
    }
    protected  function saveAnalysis($url)

    {
        $mas_html_home = $this->saveCurl($url);
        preg_match_all("/<ul\sclass=\"sub-categories-menu\">([\s*\S*]*?)<\/ul>/", $mas_html_home, $mas_categories);
        preg_match_all("/(<li.+?>.+?<\/li>)/", $mas_categories[0][0], $mas_cat);
        foreach($mas_cat[0] as $cat) {
            preg_match_all("/<li>.*href=\"\/\/(.*?)\/\">.*<\/li>/", $cat, $url);
            $url = $url[1][0];
            preg_match_all("/<a.+?>(.+?)<\/a>/", $cat, $title);
            $level = 1;
            $mas_html_cat = $this->saveCurl($url);
            preg_match_all("/<meta\sname=\"description\"\scontent=\"([^<>]*?)\"\s*\/>/", $mas_html_cat, $meta_description);

            var_dump("---Caterory---");
            var_dump($title[1][0]);

            $res = Categories::updateOrCreate(
                [
                    'url' => $url,
                ],
                [
                    'title' => $title[1][0],
                    'level' => $level,
                    'meta_description' => $meta_description[1][0],

                ]
            );
            $parent_id = $res->id;
            preg_match_all("/(<div\sclass=\"jsx-[0-9]+\sbox-wrapper\">[\s*\S*]*?<\/div>)/", $mas_html_cat, $mas_sub_cat);
            foreach ($mas_sub_cat[0] as $sub_cat) {
                preg_match_all("/.*href=\"(.*?)\".*/", $sub_cat, $sub_url);
                if (isset($sub_url[1][0])) {
                    $url = "alternativeto.net".$sub_url[1][0];
                    $page = 1;
                    dispatch(new ParsingCategory($url,$parent_id,$page))->onQueue('analytics');
                }
            }
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
