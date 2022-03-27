<?php 

function getTime() 
{
    $unixTime = time();
    $dt = new DateTime("@$unixTime");
    return $dt->format('Y-m-d H:i:s');
}
function showStatus($mess) 
{
    print getTime() . ' - ' . $mess . "\n";
}
function env($mess) 
{
    $env = include('env.php');
    return $env[$mess];
}


