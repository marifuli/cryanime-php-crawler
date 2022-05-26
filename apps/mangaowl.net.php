<?php 


showStatus('mangaowl started');

$req = Http::getHtml(env('mangaowl'));
showStatus('Got first request');

echo $req->status;
if($req->status == 200)
{
    echo ' - Working';
}