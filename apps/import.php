<?php 
exit;
$main_db = new mysqli('localhost', 'root', '', 'cryanime');
for ($i=2000; $i < 975115; $i++) 
// for ($i=1; $i < ; $i++) 
{ 
    $mangas = $db->query('select * from manga_chapter where id = '.$i.' limit 1');
    while($row = $mangas->fetch_assoc())
    {
        $req = (object) $row;
        $req->pages = json_decode($req->uploaded, true);
        $chapter = null;
        if(str_contains($req->title, 'Chapter'))
        {
            $text = explode('Chapter', $req->title);
            $text = trim( $text[1] );
            $text = explode(':', $text);
            $text = trim( $text[0] );
            $text =  filter_var( $text, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
            if(is_numeric($text))
            {
                $chapter = $text;
            }
        }
        Http::addMangaChapter([
            'manganato_url' => $req->manga_id,
            'chapter' => $chapter,
            'title' => $req->title,
            'pages' => $req->pages,
        ]);
        showStatus('Added chapter ' . $i);
    }
}


exit;
$mangas = $db->query('select * from manga_chapter limit ');
while($row = $mangas->fetch_assoc())
{
    $req = (object) $row;
    try {
        Http::addManga([
            "name" => $req->name,
            "alt_names" => $req->alt_names,
            "image" => $req->image,
            "released_on" => $req->released_on,
            "genres" => $req->genres,
            "authors" => null,
            "status" => null,
            "type" => null,
            "description" => str_replace(
                                'Description :', '', 
                                strip_tags($req->description)
                            ),
            "manganato_url" => $req->manga_id,
        ]);
        showStatus('Manga added ' . $req->id);
    } catch (\Throwable $th) {
        //throw $th;
        showStatus('Manga Failed ' . $req->id);
    }
    
}