<?php 
require 'vendor/autoload.php';
require 'DOMHtml.php';
require 'Http.php';
require 'helpers.php';

$db_config = (object) env('db');
$db = new mysqli(
    $db_config->host,
    $db_config->user,
    $db_config->pass,
    $db_config->name,
    $db_config->port,
);
if($db->connect_error)
{
    die('DB not conntect');
}
require 'config.php';
