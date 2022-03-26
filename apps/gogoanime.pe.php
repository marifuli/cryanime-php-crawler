<?php 


showStatus('Gogoanime started');

$req = Http::getHtml($env['gogoanime']);
showStatus('Got first request');

if($req->status == 200)
{
    $html = $req->response;
    showStatus('Entering loop for sub,dub, ona');

    $domain = $env['gogoanime'];
    $img_link = $html->find('img.logo.show', 0)->src;
    $parse = parse_url($img_link);
    $domain = 'https://' . $parse['host'] ; 
    // var_dump($domain);
    $anime_data = [
        // 'link_id' => [ 'title' => 'more data']
    ];

    for ($i=0; $i < 3; $i++) 
    { 
        $type = $i;
        for ($page=0; $page < 5; $page++) 
        { 
            $ajax = 'https://ajax.gogo-load.com/ajax/page-recent-release.html?page='.($page + 1).'&type='.($type + 1);
            $latest = Http::getHtml($ajax);
            if($latest->status == 200)
            {
                $latest_html = $latest->response;
                
                foreach($latest_html->find('.last_episodes li a') as $element)
                {
                    $ep_ = Http::getHtml($domain . $element->href);
                    if($ep_->status == 200)
                    {
                        $ep_html = $ep_->response;
                        $anime_url = $domain . $ep_html->find('.anime_video_body_cate .anime-info a', 0)->href;
                        //- load anime data if not aadded
                        if(!isset($anime_data[$anime_url]))
                        {
                            $anime_ = Http::getHtml($anime_url);
                            $anime_html = $anime_->response;
                            if($anime_->status == 200)
                            {
                                $data = [
                                    ''
                                ]; 

                                // TODO: make a api call and update the data
                            }
                        }
                        die;
                    }
                }
            }
        }
    }
}