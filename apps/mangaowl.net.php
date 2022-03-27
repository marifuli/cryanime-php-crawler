<?php 


showStatus('mangaowl started');

$req = Http::getHtml(env('mangaowl'));
showStatus('Got first request');

if($req->status == 200)
{

}