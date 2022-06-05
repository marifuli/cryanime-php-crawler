<?php 


showStatus('manganato started');

$domain = env('manganato');
$req = Http::getHtml($domain);
showStatus('Got first request');
// echo $req->status;
/**
 * Has Pages for latest => https://readm.org/latest-releases/502
 */
$headers = [
    'accept' =>  '*/*',
    'cookie' =>  '_lscache_vary=c708cf74fdcede54cc79e286be7d8bad; zone-cap-4598496=1',
    'origin' =>  'https://manganato.com',
    'pragma' =>  'no-cache',
    'referer' =>  'https://manganato.com/',
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
    for ($i=1; $i < 1317; $i++) 
    { 
        showStatus('Going to get the list pages');
        $pages = Http::getHtml('https://manganato.com/genre-all/' . $i);
        showStatus('In page ' . $i);

        if($pages->status == 200)
        {
            $pages_html = $pages->response;
           
            foreach($pages_html->find('.panel-content-genres .content-genres-item a.genres-item-img') as $element)
            {
                $manga_link = $element->href;
                // sleep(4);
                $manga = Http::getHtml($manga_link, $headers);
                if($manga->status == 200)
                {
                    $foreach_continue = true;
                    $manga_html = $manga->response;
                    // var_dump($manga_html);exit;
                    if(!$manga_html)
                    {
                        $arr = file_get_contents('manganato.failed_data.json');
                        $arr = $arr . $manga_link . "\n";
                        file_put_contents('manganato.failed_data.json', $arr);
                        $foreach_continue = false;
                    }
                    if( $foreach_continue )
                    {
                        $title = $manga_html->find('.panel-story-info h1', 0)->innertext;
                        $alt_names = $manga_html->find('div.container-main-left > div.panel-story-info > div.story-info-right > table > tbody tr:nth-child(1) td.table-value h2', 0)->innertext ?? '';
                        // echo $alt_names;exit;
                        $released_on = '';
                        $genres = [];
                        if($manga_html->find('div.panel-story-info > div.story-info-right > table > tbody > tr:nth-child(4) > td.table-value a', 0))
                        {
                            foreach($manga_html->find('div.panel-story-info > div.story-info-right > table > tbody > tr:nth-child(4) > td.table-value a') as $gen)
                            {
                                $genres[] = $gen->innertext;
                            }
                        }
                        $genres = join(', ', $genres);

                        $authors = $manga_html->find('body > div.body-site > div.container.container-main > div.container-main-left > div.panel-story-info > div.story-info-right > table > tbody > tr:nth-child(2) > td.table-value', 0)->innertext ?? '';
                        $status = $manga_html->find('body > div.body-site > div.container.container-main > div.container-main-left > div.panel-story-info > div.story-info-right > table > tbody > tr:nth-child(3) > td.table-value', 0)->innertext ?? '';
                        $type = '';
                        $description = $manga_html->find('.panel-story-info-description', 0) ? $manga_html->find('.panel-story-info-description', 0)->innertext : '';
                        $image = $manga_html->find('.info-image img', 0) ? $manga_html->find('.info-image img', 0)->src : '';
                        $manga_id = $manga_link;
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
                            foreach($manga_html->find('.row-content-chapter a.chapter-name') as $link)
                            {
                                // echo $link->href;exit;
                                $chap_link = $link->href;
                                $chap_title = $link->innertext ?? ''; 
                                // sleep(4);
                                $chapter = Http::getHtml($chap_link, $headers);
                                if($chapter->status == 200)
                                {
                                    $chapter_html = $chapter->response;
                                    $title = $chap_title;
                                    $chap_number = explode('-', $chap_link);
                                    $chap_number = trim($chap_number[1]);
                                    $images = [];
                                    foreach($chapter_html->find('.container-chapter-reader img') as $im) 
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
                                        showStatus('Chapter added');
                                    }
                                    else 
                                    {
                                        showStatus('Failed to add chapter');
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