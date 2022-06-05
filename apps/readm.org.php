<?php 


showStatus('readm.org started');

$domain = env('readm.org');
$req = Http::getHtml($domain);
showStatus('Got first request');
// echo $req->status;
/**
 * Has Pages for latest => https://readm.org/latest-releases/502
 */
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

if($req->status == 200)
{
    for ($i=1; $i < 503; $i++) 
    { 
        showStatus('Going to get the list pages');
        $pages = Http::getHtml('https://readm.org/latest-releases/' . $i);
        showStatus('In page ' . $i);
        if($pages->status == 200)
        {
            $pages_html = $pages->response;
           
            foreach($pages_html->find('.latest-updates h2 a') as $element)
            {
                $manga_link = $domain . $element->href;
                // sleep(4);
                $manga = Http::getHtml($manga_link, $headers);
                if($manga->status == 200)
                {
                    $foreach_continue = true;
                    $manga_html = $manga->response;
                    if(!$manga_html)
                    {
                        $arr = file_get_contents('readm.failed_data.json');
                        $arr = $arr . $manga_link . "\n";
                        file_put_contents('readm.failed_data.json', $arr);
                        $foreach_continue = false;
                    }
                    if( $foreach_continue )
                    {
                        $title = $manga_html->find('.page-title', 0)->innertext;
                        $alt_names = $manga_html->find('.sub-title', 0) ? $manga_html->find('.sub-title', 0)->innertext : '';
                        $released_on = '';
                        $genres = [];
                        if($manga_html->find('.ui.list .item :not(.label)', 0))
                        {
                            foreach($manga_html->find('.ui.list .item :not(.label)') as $gen)
                            {
                                $genres[] = $gen->innertext;
                            }
                        }
                        $genres = join(', ', $genres);

                        $authors = $manga_html->find('.first_and_last a', 0) ? $manga_html->find('.first_and_last a', 0)->innertext : '';
                        $status = $manga_html->find('.series-status', 0) ? $manga_html->find('.series-status', 0)->innertext : '';
                        $type = $manga_html->find('.media-meta table div:nth-child(2)', 0) ? $manga_html->find('.media-meta table div:nth-child(2)', 0)->innertext : '';
                        $description = $manga_html->find('.series-summary-wrapper p span', 0) ? $manga_html->find('.series-summary-wrapper p span', 0)->innertext : '';
                        $image = $manga_html->find('.series-profile-thumb', 0) ? $manga_html->find('.series-profile-thumb', 0)->src : '';
                        $manga_id = explode('manga/', $manga_link);
                        $manga_id = $manga_id[1];
                        // die($title);

                        $sql = "INSERT INTO `manga` (`name`, `alt_names`, `image`, `released_on`, `genres`, `authors`, `status`, `type`, `description`, `manga_id`) VALUES (?, ?, ?,?,?,?,?,?,?,?)";
                        $stmt2 = $db->prepare($sql);
                        $stmt2->bind_param(
                            'ssssssssss',
                            $title,
                            $alt_names,
                            $image,
                            $released_on,
                            $genres,
                            $authors,
                            $status,
                            $type,
                            $description,
                            $manga_id
                        );
                        
                        if ($stmt2->execute()) 
                        {
                            showStatus('Added Manga');
                            // document.querySelectorAll('.episodes-box .item.season_start .truncate a')
                            foreach($manga_html->find('.episodes-box .item.season_start .truncate a') as $link)
                            {
                                // echo $link->href;
                                $chap_link = $domain . $link->href;
                                // sleep(4);
                                $chapter = Http::getHtml($chap_link, $headers);
                                if($chapter->status == 200)
                                {
                                    $chapter_html = $chapter->response;
                                    $title = $chapter_html->find('.light-title', 0)->innertext;
                                    $chap_number = explode('ter', $title);
                                    $chap_number = trim($chap_number[1]);
                                    $images = [];
                                    foreach($chapter_html->find('.ch-images.ch-image-container img') as $im) 
                                    {
                                        $images[] = $im->src;
                                    }
                                    $images = json_encode($images);
                                    $sql = "INSERT INTO `manga_chapter` (`manga_id`, `chapter_number`, `title`, `uploaded`) VALUES (?,?,?,?);";
                                    $stmt2 = $db->prepare($sql);
                                    $stmt2->bind_param(
                                        'ssss',
                                        $manga_id,
                                        $chap_number,
                                        $title,
                                        $images
                                    );
                                    
                                    if ($stmt2->execute()) 
                                    {
                                        // showStatus('Chapter added');
                                    }
                                }
                            }
                        }
                        else 
                        {
                            showStatus('Failed to add Manga');
                        }
                        $stmt2->close();
                    }
                }
            }
        }
        // exit;
    }
}