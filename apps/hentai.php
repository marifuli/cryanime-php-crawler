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
            'vars' => '{"_wpresults":"afd690ed98","taxonomy":"none","search":"none","term":"none","type":"episodes","genres":[],"years":[],"sort":"1","page":'.$page_number.'}',
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
        showStatus('getting the json');

        $page = Http::postFormJson($latest_uri, $data, $headers);
        if(
            $page->status == 200
        )
        {
            showStatus('Got the json');

            if(!$page->response->data->next)
            {
                $continue = false;
            }
            $page_html = str_get_html($page->response->data->html);
            foreach($page_html->find('article a.lnk-blk') as $a)
            {
                showStatus('getting to the episode');
                $episode = Http::getHtml($a->href);
                if($episode->status == 200)
                {
                    showStatus('episode loaded');
                    
                    $episode_html = $episode->response;
                    if(
                        true
                    )
                    {   
                        $iframe = $episode_html->find('.player.mgt.mgb2 iframe[allowfullscreen]', 0)->src;
                        if(!str_contains($iframe, 'http'))
                        {
                            $iframe = $episode_html->find('.player.mgt.mgb2 iframe[allowfullscreen]', 0)->getAttribute('data-litespeed-src');
                        }

                        $title = trim( $episode_html->find('.anime-cn.clb h1', 0)->plaintext );
                        $poster = trim( $episode_html->find('.anime-tb.pctr.dn.c-db img', 0)->getAttribute('data-src') );
                        
                        $series = trim( $episode_html->find('.player-nv.df.aic.fz12.b-fz16 a', 0)->href );
                        $series = str_replace('/', '', explode('.com/hentai/', $series)[1] );

                        $year = trim( $episode_html->find('.anime-cn.clb div a', 0)->plaintext );
                        $released_on = trim( $episode_html->find('.anime-cn.clb div span.mgr.mgb', 5)->innertext );
                        $quality = trim( $episode_html->find('.anime-cn.clb div a', 1)->plaintext );
                        $description = trim( $episode_html->find('.description.link-co.mgb2', 0)->plaintext );
                        $alt_name = trim( 
                            $episode_html->find('.description.link-co.mgb2 p', 1)?->plaintext 
                        );
                        $animidhentai_link = $a->href;
                        $ep_data = compact(
                            'title',
                            'series',
                            'released_on',
                            'quality',
                            'description',
                            'animidhentai_link',
                            'alt_name',
                            'poster',
                        );
                        $links = [
                            'iframe' => $iframe
                        ];

                        //- check the oother site if it has any video link
                        showStatus('Checking the exernal site for mp4');
                        $search = 'https://tube.hentaistream.com/?s=' . urlencode($title);
                        $searchReq = Http::getHtml($search);
                        if($searchReq->status == 200)
                        {
                            $searchHtml = $searchReq->response;
                            if($searchHtml->find('.bodyleft .post', 0))
                            { 
                                $ep_data['thumbnail'] = $searchHtml->find('.bodyleft .post .postimg .thumbIMG', 0)->src; 

                                showStatus('Checking the exernal site for mp4 - step 2');

                                $search2 = $searchHtml->find('.bodyleft .post .postimg a', 0)->href;
                                $searchReq2 = Http::getHtml($search2);
                                if($searchReq2->status == 200)
                                {
                                    $searchHtml2 = $searchReq2->response;
                                    if(
                                        $searchHtml2->find('.videohere iframe[allowfullscreen]', 0)
                                    )
                                    {
                                        showStatus('Checking the exernal site for mp4 - step 3');

                                        $searchReq3 = Http::getHtml(
                                            $searchHtml2->find('.videohere iframe[allowfullscreen]', 0)->src
                                        );
                                        if($searchReq3->status == 200)
                                        {
                                            $searchHtml3 = $searchReq3->response;
                                            if($searchHtml3->find('video source[type=video/mp4]', 0))
                                            {
                                                $video_link = $searchHtml3->find('video source[type=video/mp4]', 0)->src;
                                                showStatus('checking mp4 validity');
                                                if(
                                                    Http::isValidLink($video_link, 'video/mp4')
                                                )
                                                {
                                                    $links['video'] = $video_link;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $ep_data['links'] = json_encode($links);
                        showStatus('Adding to database');
                        save_data($ep_data, $db);
                        die;
                    }
                }
            }
        }


        $page_number++;
        $continue = false;
    }
}

function save_data($data, $conn)
{
    // var_dump($data);
    $title = $data['title'] ;
    $alt_name = $data['alt_name'] ;
    $series = $data['series'] ;
    $year = $data['year'] ?? '' ;
    $quality = $data['quality'] ;
    $released_on = $data['released_on'] ;
    $description = $data['description'] ;
    $genres = $data['genres'] ?? '';
    $links = $data['links'] ;
    $animidhentai_link = $data['animidhentai_link'] ;
    $thumbnail = $data['thumbnail'] ?? '' ;
    $poster = $data['poster'] ?? '' ;

    $sql = "SELECT * FROM animeidhentai WHERE title = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $title);
    $stmt->execute();

    if($stmt->get_result()->num_rows)
    {
        showStatus('Already Episode added');

        $sql = "UPDATE `animeidhentai` 
        SET `alt_name` = ?, `series` = ?, `year` = ?, `quality` = ?, `released_on` = ?, `description` = ?, `genres` = ?, `links` = ?, `animidhentai_link` = ?, `poster` = ?, `thumbnail` = ?
        WHERE `title` = ? 
        LIMIT 1
        ";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param(
            'ssssssssssss',
            $alt_name,
            $series,
            $year,
            $quality,
            $released_on,
            $description,
            $genres,
            $links,
            $animidhentai_link,
            $poster,
            $thumbnail,
            $title
        );
        
        if ($stmt2->execute()) 
        {
            showStatus('Updated Episode');
        }
        else 
        {
            showStatus('Failed to Update Episode');
        }
        $stmt2->close();
    }else
    {
        $sql = "INSERT INTO `animeidhentai` (`title`, `alt_name`, `series`, `year`, `quality`, `views`, `released_on`, `description`, `genres`, `links`, `animidhentai_link`, `poster`, `thumbnail`) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param(
            'ssssssssssss',
            $title,
            $alt_name,
            $series,
            $year,
            $quality,
            $released_on,
            $description,
            $genres,
            $links,
            $animidhentai_link,
            $poster,
            $thumbnail
        );
        
        if ($stmt2->execute()) 
        {
            showStatus('Added Episode');
        }
        else 
        {
            showStatus('Failed to add Episode');
        }
        $stmt2->close();
    }
    $stmt->close();
}