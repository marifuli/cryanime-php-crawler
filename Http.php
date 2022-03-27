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

    static function addAnime ($data) 
    {
        $client = new \GuzzleHttp\Client(["base_uri" => env('api.base_uri')]);
        $options = [
            'json' => $data
        ]; 
        $response = $client->post(env('api.addAnime') . '?apikey=' . env('apiKey'), $options);

        return $response->getBody()->getContents();
    }
    static function addEpisode ($data) 
    {
        $client = new \GuzzleHttp\Client(["base_uri" => env('api.base_uri')]);
        $options = [
            'json' => $data
        ]; 
        $response = $client->post(env('api.addEpisode') . '?apikey=' . env('apiKey'), $options);

        return $response->getBody()->getContents();
    }
}