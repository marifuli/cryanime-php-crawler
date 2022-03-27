<?php 


showStatus('animeidhentai started');

$req = Http::getHtml(env('animeidhentai'));
showStatus('Got first request');

if($req->status == 200)
{
    // echo $req->response_string;
    showStatus('Got into the site');

    $continue = true;
    $page_number = 1;
    while($continue)
    {
        $latest_uri = 'https://animeidhentai.com/wp-admin/admin-ajax.php';
        $data = [
            'action' => 'action_results', 
            'vars' => '{"_wpresults":"afd690ed98","taxonomy":"none","search":"none","term":"none","type":"episodes","genres":[],"years":[],"sort":"1","page":2}',
        ];
        $headers = [
            'accept' =>  '*/*',
            'cookie' =>  '_lscache_vary=c708cf74fdcede54cc79e286be7d8bad; zone-cap-4598496=1',
            'origin' =>  'https://animeidhentai.com',
            'pragma' =>  'no-cache',
            'referer' =>  'https://animeidhentai.com/?s=',
            'sec-fetch-dest' =>  'empty',
            'sec-fetch-mode' =>  'cors',
            'sec-fetch-site' =>  'same-origin',
            'sec-gpc' =>  1,
            'user-agent' =>  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.88 Safari/537.36',
            'x-id' =>  true,
            'x-requested-with' =>  'XMLHttpRequest',
            'x-wp-nonce' =>  'afd690ed98',
        ];
        
        $page = Http::postFormJson($latest_uri, $data, $headers);
        if(
            $page->status == 200
        )
        {
            $page_html = str_get_html($page->response->data->html);
            foreach($page_html->find('article a') as $a)
            {
                echo $a->href . "\n";
            }
        }


        $page_number++;
        $continue = false;
    }
}