<?php 
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'cryanime_crawler',
        'port' => '3306',
        'user' => 'root',
        'pass' => ''
    ],
    'apiKey' => '4efa1878-8a08-437c-b136-0e5aced9ff89',
    'api.base_uri' => 'https://api.cryanime.com',
    'api.addAnime' => '/api/anime/add',
    'api.addEpisode' => '/api/episode/add',
    'api.addHentai' => '/api/hentai/add',
    
    'gogoanime' => 'https://gogoanime.sk',
    'mangaowl' => 'https://mangaowl.net',
    'readm.org' => 'https://readm.org',
    'manganato' => 'https://manganato.com',

    'animeidhentai' => 'https://animeidhentai.com', // priority 1
    'tube.hentaistream' => 'https://tube.hentaistream.com', // priority 2
];