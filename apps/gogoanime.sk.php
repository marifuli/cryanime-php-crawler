<?php 


showStatus('Gogoanime started');

$req = Http::getHtml(env('gogoanime'));
showStatus('Got first request');

if($req->status == 200)
{
    $html = $req->response;
    showStatus('Entering loop for sub,dub, ona');

    $domain = env('gogoanime');
    $img_link = $html->find('img.logo.show', 0)->src;
    $parse = parse_url($img_link);
    $domain = 'https://' . $parse['host'] ; 
    // var_dump($domain);
    $anime_data = [
        // 'link_id' => [ 'title' => 'more data']
    ];
    $episode_counter = 1;
    $anime_counter = 1;

    for ($i=0; $i < 3; $i++) 
    { 
        $type = $i;
        for ($page=0; $page < 1; $page++) 
        { 
            $ajax = 'https://ajax.gogo-load.com/ajax/page-recent-release.html?page='.($page + 1).'&type='.($type + 1);
            $latest = Http::getHtml($ajax);
            if($latest->status == 200)
            {
                $latest_html = $latest->response;
                
                foreach($latest_html->find('.last_episodes li a') as $element)
                {
                    $ep_ = Http::getHtml($domain . $element->href);
                    $ep_url_id = $element->href;
                    $ep_url_id = str_replace('/', '', $ep_url_id);
                    $ep_html = $ep_->response;

                    if(
                        $ep_->status == 200 && $ep_html->find('.anime_video_body_cate .anime-info a', 0)
                    )
                    { 
                        $anime_url = $domain . $ep_html->find('.anime_video_body_cate .anime-info a', 0)->href;
                        $anime_url_id = explode('category/', $anime_url)[1];

                        //- load anime data if not aadded
                        if(!isset($anime_data[$anime_url]))
                        {
                            $anime_ = Http::getHtml($anime_url);
                            $anime_html = $anime_->response;
                            
                            if(
                                $anime_->status == 200 && 
                                $anime_html->find('.anime_info_body .anime_info_body_bg img', 0) && 
                                $anime_html->find('.anime_info_body .anime_info_body_bg h1', 0) &&
                                $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 1) &&
                                $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 2) &&
                                $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 3) &&
                                $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 4) &&
                                $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 5) &&
                                $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 0)
                            )
                            {
                                $data = [
                                    'image' => $anime_html->find('.anime_info_body .anime_info_body_bg img', 0)->src,
                                    'name' => $anime_html->find('.anime_info_body .anime_info_body_bg h1', 0)->innertext,
                                    'alt_names' => trim(explode('Other name:', $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 5)->plaintext)[1]),
                                    'genre' => trim(explode('Genre:', $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 2)->plaintext)[1]),
                                    'status' => trim(explode('Status: ', $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 4)->plaintext)[1]),
                                    'released_on' => trim(explode('Released:', $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 3)->plaintext)[1]),
                                    'description' => trim(explode('Plot Summary:', $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 1)->plaintext)[1]),
                                    'type' => trim(explode('Type:', $anime_html->find('.anime_info_body .anime_info_body_bg p.type', 0)->plaintext)[1]),
                                    'url_id' => trim($anime_url_id),
                                ]; 
                                $anime_data[$anime_url_id] = $data;

                                // var_dump($data); 
                                showStatus($anime_counter . ' - ' . Http::addAnime($data) . " - Added Anime - " . $anime_url_id);
                                $anime_counter++;
                            }
                        }
                        $iframes = [];
                        foreach($ep_html->find('.anime_muti_link a') as $ifr)
                        {
                            $link = $ifr->getAttribute('data-video');
                            if(!str_contains($link, 'http'))
                            {
                                $link = 'https:' .  $link;
                            }
                            $iframes[] = $link; 
                        }
                        if(
                            count($iframes) && $ep_html->find('.anime_video_body .default_ep', 0)
                        )
                        {
                            $ep_data = [
                                'ep_url_id' => trim($ep_url_id),
                                'anime_url_id' => trim($anime_url_id),
                                'iframes' => $iframes,
                                'ep_number' => $ep_html->find('.anime_video_body .default_ep', 0)->value,
                            ];
                            // var_dump($ep_data); die;
                            // TODO: make a api call and update the Epsisode data
                            showStatus($episode_counter . ' - ' . Http::addEpisode($ep_data) . " - Added Episode - " . $ep_url_id);
                            $episode_counter++;
                        }
                        // die;
                    }
                }
            }
        }
    }
}