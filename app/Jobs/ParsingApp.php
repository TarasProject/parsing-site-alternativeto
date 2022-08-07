<?php

namespace App\Jobs;

use App\Models\App;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ParsingApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $url_app;
    protected $categories_id;
    protected $page;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$data_categories_id,$data_page)
    {
        $this->url_app = $data;
        $this->categories_id = $data_categories_id;
        $this->page = $data_page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->saveAnalysis($this->url_app,$this->categories_id,$this->page);
    }
    protected  function saveAnalysis($url_app,$categories_id,$page)

    {
        var_dump('---JOB-App---');

        if (!App::where('url_hash', md5($url_app))->first()) {

                var_dump($url_app);
            $mas_html_app = $this->saveCurl($url_app);
            preg_match_all("/(new-app-page)/",$mas_html_app,$new_app_page);
            //перевіряємо чи нова сторінка програми
            if(isset($new_app_page[0][0])) {
                var_dump("new");
                $url_app_about = $url_app."about/";
                $mas_html_app = $this->saveCurl($url_app_about);
                preg_match_all("/<h1 itemprop=\"name\">([^<>]+)<\/h1>/",$mas_html_app,$mas_title);
                $title = $mas_title[1][0];
                preg_match_all("/<span\sclass=\"num\">(\d+)<\/span>/",$mas_html_app,$mas_like);
                $like = $mas_like[1][0];
                preg_match_all("/<div\sclass=\"image-wrapper\">\s<img.*data-src=\"\/{2}(.+?)\"/",$mas_html_app,$mas_icon);
                (empty($mas_icon[1][0])) ? $icon = null : $icon = $mas_icon[1][0];
                if(preg_match_all("/<p.*>([^<>]+)<span\sclass=\"creator\">/",$mas_html_app,$mas_anonce)){}
                else {preg_match_all("/<p.*>([^<>]+)<\/p>/",$mas_html_app,$mas_anonce);}
                $anonce = $mas_anonce[1][0];
//
                preg_match_all("/<div.*main-info\">([\s*\S*]*class=\"banner\">)/",$mas_html_app,$mas_discription);
                $description = preg_replace("/(?:<|&lt;).+?(?:>|&gt;)/", '', $mas_discription[1][0]);
//                var_dump($description);
                preg_match_all("/<span\sclass=\"label\slabel-license\">(.*)<\/span>/",$mas_html_app,$mas_license);
                $license = $mas_license[1][0];

                preg_match_all("/<span.*platform-icon\"?>([^<>]+)<\/span>/",$mas_html_app,$mas_platforms);
                $platforms = implode(", ", $mas_platforms[1]);
//                               var_dump($platforms);
                preg_match_all("/<span>Discover\s(\d+).*<\/span>/",$mas_html_app,$mas_count_alternatives);
                $count_alternatives = $mas_count_alternatives[1][0];
//                                var_dump($count_alternatives);

                preg_match_all("/<a.*\stitle=\"Visit\s(.*)Official\ssite/",$mas_html_app,$mas_company);
                $company = $mas_company[1][0];
                preg_match_all("/<a\shref=\"(.*?)\".*website-link.*>/",$mas_html_app,$mas_website_link);
                $website = $mas_website_link[1][0];
                //сайт компанії і сайт програми окремо немає у новій версії сторінки, тому
                $company_website = $mas_website_link[1][0];

                $screenshots = "<h2>Screenshots</h2>";
                $divide_page = explode($screenshots,$mas_html_app);
                preg_match_all("/<a.*screenshot-thumbnail.+href=\"\/\/(.*)?\">/",$divide_page[1],$mas1_sceenshots);
                preg_match_all("/<a.*screenshot-thumbnail.*\s.*img-responsive\"\ssrc=\"(.*)\"\salt/",$divide_page[1],$mas2_sceenshots);
                $mas_sceenshots = array_merge($mas1_sceenshots[1],$mas2_sceenshots[1]);
                $sceenshots_urls = implode(", ", $mas_sceenshots);
//
                preg_match_all("/<span\sclass='label.*?>([^<>]+)<\/span>/",$mas_html_app,$mas_tags);
                $tags = implode(", ", $mas_tags[1]);
//                var_dump($tags);
                preg_match_all("/(<p>.*applicationCategory[\s*\S*]*?<\/p>)/",$mas_html_app,$mas_categories);
                preg_match_all("/<a.+?>(.+?)<\/a>/",$mas_categories[0][0],$categories);
                $categories = implode(", ", $categories[1]);

                preg_match_all("/<a\sdata-link-action.*>(.*)<\/a>/",$mas_html_app,$mas_alternatives);
                $alternatives = implode(", ", $mas_alternatives[1]);

                preg_match_all("/<a.*data-link-action=\"AppStores.*href=\"(.*)\">.*<\/a>/",$mas_html_app,$mas_app_stores);
                $app_stores = implode(", ", $mas_app_stores[1]);

                preg_match_all("/<meta\sname=\"description\"\scontent=\"([\s*\S*]*?)\">?/",$mas_html_app,$mas_meta_description);
                $meta_description = $mas_meta_description[1][0];

                preg_match_all("/<span.*ratingValue\">(.*)<\/span>/",$mas_html_app,$mas_ratings);
                (empty($mas_ratings[1][0])) ? $ratings = null : $ratings = $mas_ratings[1][0];
//                var_dump($mas_ratings);

                $res = App::updateOrCreate(
                    [
                        'url_hash' => md5($url_app),
                    ],
                    [
                        'url' => $url_app,
                        'url_hash' => md5($url_app),
                        'title' => $title,
                        'page' => $page,
                        'like' => $like,
                        'icon' => $icon,
                        'anonce' => $anonce,
                        'company_website' => $company_website,
                        'website' => $website,
                        'company' => $company,
                        'description' => $description,
                        'tags' => $tags,
                        'platforms' => $platforms,
                        'count_alternatives' => $count_alternatives,
                        'categories' => $categories,
                        'sceenshots_urls' => $sceenshots_urls,
                        'alternatives' => $alternatives,
                        'license' => $license,
                        'app_stores' => $app_stores,
                        'meta_description' => $meta_description,
                        'ratings' => $ratings,

                    ]
                );

            } else {
                var_dump("old");
                preg_match_all("/<section.*bluebox[\s*\S*]*<\/section>/",$mas_html_app,$mas_bluebox);
//                var_dump($mas_bluebox[0][0]);

                preg_match_all("/<h1 itemprop=\"name\">([^<>]+)<\/h1>/",$mas_bluebox[0][0],$mas_title);
                $title = $mas_title[1][0];
//                            var_dump($title);
                preg_match_all("/<span\sclass=\"num\">(\d+)<\/span>/",$mas_bluebox[0][0],$mas_like);
                $like = $mas_like[1][0];
//                            var_dump($like);
                preg_match_all("/<div\sclass=\"image-wrapper\">\s<img.*data-src=\"\/{2}(.+?)\"/",$mas_bluebox[0][0],$mas_icon);
//                var_dump($mas_icon);
                (empty($mas_icon[1][0])) ? $icon = null : $icon = $mas_icon[1][0];

//                            var_dump($icon);
                if(preg_match_all("/<p.*>([^<>]+)<span\sclass=\"creator\">/",$mas_bluebox[0][0],$mas_anonce)){}
                else {preg_match_all("/<p.*>([^<>]+)<\/p>/",$mas_bluebox[0][0],$mas_anonce);}
                $anonce = $mas_anonce[1][0];
//                            var_dump($anonce)//;
                preg_match_all("/<p.*|\shref=\"(.*)\"\stitle/",$mas_bluebox[0][0],$mas_company_website);
                $company_website = $mas_company_website[1][1];
                preg_match_all("/<p.*|\shref=\".*\"\stitle=\"Go\sto(.*)Official site\">/",$mas_bluebox[0][0],$mas_company);
                $company = $mas_company[1][1];
                preg_match_all("/<a\shref=\"(.*?)\".*website-link.*>/",$mas_bluebox[0][0],$mas_website_link);
                $website = $mas_website_link[1][0];

                preg_match_all("/(<span\s.*item-desc.*>\s.*\s<\/span>)/",$mas_bluebox[0][0],$span_description);
//                            var_dump($span_description[0][0]);
                $description = preg_replace("/(?:<|&lt;).+?(?:>|&gt;)/", '', $span_description[0][0]);
//                            var_dump($description);
                $description = preg_replace("/...\sMore\sInfo\s&raquo;/", '. ', $description);
//                            var_dump($description);
                preg_match_all("/<span\sclass='label.*?>([^<>]+)<\/span>/",$mas_bluebox[0][0],$mas_tags);
                $tags = implode(", ", $mas_tags[1]);
//                            var_dump($tags);
                preg_match_all("/<li\sclass=\"label.*?>([^<>]+)<\/li>/",$mas_bluebox[0][0],$mas_platforms);
                $platforms = implode(",", $mas_platforms[1]);
//                               var_dump($platforms);
                preg_match_all("/Alternatives\s.*badge\">(\d+)<\/span>/",$mas_bluebox[0][0],$mas_count_alternatives);
                $count_alternatives = $mas_count_alternatives[1][0];
//                                var_dump($count_alternatives);

                preg_match_all("/(<p>.*applicationCategory[\s*\S*]*?<\/p>)/",$mas_bluebox[0][0],$mas_categories);
                preg_match_all("/<a.+?>(.+?)<\/a>/",$mas_categories[0][0],$categories);
                $categories = implode(", ", $categories[1]);

                preg_match_all("/<a.*screenshot-thumbnail.+href=\"\/\/(.*)?\">/",$mas_bluebox[0][0],$mas_sceenshots);
                $sceenshots_urls = implode(", ", $mas_sceenshots[1]);

                preg_match_all("/<a\sdata-link-action.*>(.*)<\/a>/",$mas_html_app,$mas_alternatives);
                $alternatives = implode(", ", $mas_alternatives[1]);

                preg_match_all("/<span\sclass=\"pricing-[a-z]+\">(.*)<\/span>/",$mas_bluebox[0][0],$mas_license);
                $license = $mas_license[1][0];

                preg_match_all("/<a.*data-link-action=\"AppStores.*href=\"(.*)\">.*<\/a>/",$mas_bluebox[0][0],$mas_app_stores);
                $app_stores = implode(", ", $mas_app_stores[1]);

                preg_match_all("/<meta\sname=\"description\"\scontent=\"([\s*\S*]*?)\">?/",$mas_html_app,$mas_meta_description);
                $meta_description = $mas_meta_description[1][0];

                preg_match_all("/<span.*ratingValue\">(.*)<\/span>/",$mas_html_app,$mas_ratings);
                (empty($mas_ratings[1][0])) ? $ratings = null : $ratings = $mas_ratings[1][0];

                $res = App::updateOrCreate(
                    [
                        'url_hash' => md5($url_app),
                    ],
                    [
                        'url' => $url_app,
                        'url_hash' => md5($url_app),
                        'title' => $title,
                        'page' => $page,
                        'like' => $like,
                        'icon' => $icon,
                        'anonce' => $anonce,
                        'company_website' => $company_website,
                        'website' => $website,
                        'company' => $company,
                        'description' => $description,
                        'tags' => $tags,
                        'platforms' => $platforms,
                        'count_alternatives' => $count_alternatives,
                        'categories' => $categories,
                        'sceenshots_urls' => $sceenshots_urls,
                        'alternatives' => $alternatives,
                        'license' => $license,
                        'app_stores' => $app_stores,
                        'meta_description' => $meta_description,
                        'ratings' => $ratings,

                    ]
                );

            }

            $app_id = $res->id;
            $app = App::find($app_id);
            $app->categories()->attach($categories_id);
        }  else {
//                $app_id = App::where('url_hash', md5($url_app))->first()->id;
//
//                if(!DB::table('app_categories')->where('app_id',$app_id)
//                    ->where('categories_id',$categories_id)->get()){
//                    $app_id->categories()->attach($categories_id);
//                }

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
