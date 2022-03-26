<?php
class Http 
{
    static function getHtml($url)
    {
        $res = new stdClass();
        $res->status = 500;
        $res->type = null;
        $res->response = null;
        $res->object = null;
        try {
            //code...
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $url);
            $res->status = $response->getStatusCode();
            $res->type = $response->getHeaderLine('content-type');
            $res->response = str_get_html($response->getBody());
            $res->response_string = $response->getBody();
            $res->object = $response;
            
        } 
        catch (\Throwable $th) 
        {
            //throw $th;
        }
        
        return $res;
    }
}