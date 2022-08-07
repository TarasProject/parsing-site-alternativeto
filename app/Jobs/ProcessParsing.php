<?php

namespace App\Jobs;

use App\Models\Alternativeto;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use phpDocumentor\Reflection\Types\Null_;

class ProcessParsing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($xml_url)
    {
        $this->url = $xml_url;
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
        //preg_match_all("/<li class=\"hidden-xs\"><a href=\"\/platform\/.*\">(.*)<\/a><\/li>/", $mas_html_home, $oss);
        $oss = ["windows", "mac", "linux", "chrome-os", "android", "iphone", "windows-phone", "blackberry", "blackberry-10", "ipad", "android-tablet", "kindle-fire", "windows-metro"];
            foreach ($oss as $os){
                //preg_match_all("/<a href=\"(.*)\">(.*)<\/a>/",$href_os,$os);
                $href_os = "https://alternativeto.net/platform/".$os."/";
                //var_dump($href_os);//var_dump($a_os);//var_dump($title_os);!

                $mas_html_href_os = $this->saveCurl($href_os);
                //var_dump($mas_html_href_os);
                preg_match_all("/<div class=\".*pagination\"><span.*next\">(Next)<\/span><\/div>/",$mas_html_href_os,$pagination);
                //var_dump($pagination[1][0]);
                $i = 1;
                while ($pagination[1][0] === "Next"){

                    $href_os_p = $href_os."?p=".$i;
                    $mas_html_href_os_p = $this->saveCurl($href_os_p);
                    preg_match_all("/(<li data-testid=.*?>.*?<\/li>)/",$mas_html_href_os_p,$apps);
                    $mas_apps[] = $apps;
                    foreach ($apps[0] as $app){
                        //var_dump($app);
                        preg_match_all("/<h2.*><a href=\"\/software\/(.*)\"\sclass.*<\/h2>/", $app, $end_url);
                        //var_dump($end_url[1][0]);
                        $url_app = "https://alternativeto.net/software/".$end_url[1][0];
                        //var_dump($url);
                        preg_match_all("/<h2.*><a.*>(.*)<\/a><\/h2>/", $app, $title_app);
                        $title = $title_app[1][0];
                        //var_dump($title);
                        preg_match_all("/<button\stitle=\"Like.*>(.*)<\/span><\/button>/", $app, $like);
                        //var_dump($like[1][0]);
                        $like = $like[1][0];
                        //переходимо насторінку програми
                        $mas_html_app = $this->saveCurl($url_app);
                        preg_match_all("/(new-app-page)/",$mas_html_app,$new_app_page);

                        //перевіряємо чи нова сторінка програми
                        if(isset($new_app_page[0][0])) {
                            var_dump("new");
                            $res = Alternativeto::updateOrCreate(
                                [
                                    'url' => $url_app,
                                ],
                                [
                                    'os' => ucfirst($os),
                                    'url_hash' => md5($url_app),
                                    'title' => $title,
                                    'like' => $like,
                                    'icon' => "NO icon, new version",

                                ]
                            );

                        } else {
                            var_dump("old");
                            preg_match_all("/<div\sclass=\"image-wrapper\">\s<img.*data-src=\"\/{2}(.+?)\"/",$mas_html_app,$mas_icon);
                            $icon = $mas_icon[1][0];
//                            var_dump($icon);
                            preg_match_all("/<p.*\s(.*)\s<span(.*\s){3}<\/p>/",$mas_html_app,$mas_anonce);
                            $anonce = $mas_anonce[1][0];
//                            var_dump($anonce);
                            preg_match_all("/<p.*|\shref=\"(.*)\"\stitle/",$mas_html_app,$mas_company_website);
                            $company_website = $mas_company_website[1][1];
                            preg_match_all("/(<span\s.*item-desc.*>\s.*\s<\/span>)/",$mas_html_app,$span_description);
//                            var_dump($span_description[0][0]);
                            $description = preg_replace("/(?:<|&lt;).+?(?:>|&gt;)/", '', $span_description[0][0]);
//                            var_dump($description);
                            $description = preg_replace("/...\sMore\sInfo\s&raquo;/", '. ', $description);
                            var_dump($description);


                            $res = Alternativeto::updateOrCreate(
                                [
                                    'url_hash' => md5($url_app),
                                ],
                                [
                                    'url' => $url_app,
                                    'os' => ucfirst($os),
                                    'url_hash' => md5($url_app),
                                    'title' => $title,
                                    'like' => $like,
                                    'icon' => $icon,
                                    'anonce' => $anonce,
                                    'company_website' => $company_website,
                                    'description' => $description,

                                ]
                            );

                        }

                    }
                    preg_match_all("/<div class=\".*pagination\"><span.*next\">(Next)<\/span><\/div>/",$mas_html_href_os_p,$pagination);
                    if (!isset($pagination[1][0])) {
                        $pagination[1][0] = null;
                        //var_dump($pagination[1][0]);
                    }
                    $i++;
                    //dd($apps);
                }
            break;
            }
        var_dump("end");
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


