<?php 

function getTime() 
{
    $unixTime = time();
    $dt = new DateTime("@$unixTime");
    return $dt->format('Y-m-d H:i:s');
}
function showStatus($mess) 
{
    print $mess . ' - ' . getTime() . "\n";
}


