<?php 


showStatus('readm.org started');

$domain = env('readm.org');
$req = Http::getHtml($domain);
showStatus('Got first request');
// echo $req->status;
/**
 * Has Pages for latest => https://readm.org/latest-releases/502
 */

if($req->status == 200)
{
    for ($i=1; $i < 503; $i++) 
    { 
        showStatus('Going to get the list pages');
        $pages = Http::getHtml('https://readm.org/latest-releases/' . $i);
        if($pages->status == 200)
        {
            $pages_html = $pages->response;
            
            foreach($pages_html->find('.latest-updates h2 a') as $element)
            {
                $manga_link = $domain . $element->href;
                $manga = Http::getHtml($manga_link);
                if($manga->status == 200)
                {
                    $manga_html = $manga->response;
                    echo $manga_html->find('.page-title', 0)->innertext;
                    $sql = "INSERT INTO `manga` () VALUES ()";
                    $stmt2 = $conn->prepare($sql);
                    $stmt2->bind_param(
                        'ss',
                        $title,
                        $alt_name,
                    );
                    
                    if ($stmt2->execute()) 
                    {
                        showStatus('Added Manga');
                    }
                    else 
                    {
                        showStatus('Failed to add Manga');
                    }
                    $stmt2->close();
                }
            }
        }
        exit;
    }
}