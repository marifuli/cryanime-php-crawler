<?php 

// just add the file you want to run here

// include './apps/gogoanime.pe.php';
// include './apps/mangaowl.net.php';
//include './apps/hentai.php';
// include './apps/hentai_import.php';
// include './apps/readm.org.php';
if(!isset($argv[1]))
{
    die('Please define an app name!');
}
include 'apps/' . $argv[1] . '.php';

