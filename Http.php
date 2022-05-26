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
    static function postJson($url, $data)
    {
        $res = new stdClass();
        $res->status = 500;
        $res->type = null;
        $res->response = null;
        $res->object = null;
        try {
            //code...
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, ['json' => $data]);
            $res->status = $response->getStatusCode();
            $res->type = $response->getHeaderLine('content-type');
            $res->response = $response->getBody();
            $res->object = $response;
            
        } 
        catch (\Throwable $th) 
        {
            //throw $th;
        }
        
        return $res;
    }
    static function postFormJson($url, $data, $headers = [])
    {
        $res = new stdClass();
        $res->status = 500;
        $res->type = null;
        $res->response = null;
        $res->object = null;
        try {
            //code...
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, ['form_params' => $data, 'headers' => $headers]);
            $res->status = $response->getStatusCode();
            $res->type = $response->getHeaderLine('content-type');
            $res->response = $response->getBody();
            $res->object = $response;
            if(str_contains($res->type, 'application/json'))
            {
                $res->response = json_decode($res->response);
            }
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
    static function addHentai ($data) 
    {
        $client = new \GuzzleHttp\Client(["base_uri" => env('api.base_uri')]);
        $options = [
            'json' => $data
        ]; 
        $response = $client->post(env('api.addHentai') . '?apikey=' . env('apiKey'), $options);

        return $response->getBody()->getContents();
    }



    static function isValidLink($link, $contentType = null)
    {
        try {
            $array = get_headers($link);
            if($array && is_array($array))
            {
                if($contentType)
                {
                    $data = false;
                    foreach($array as $type)
                    {
                        if(str_contains($type, $contentType))
                        {
                            $data = true;
                        }
                    }
                    return $data;
                }
                return true;
            }
        } 
        catch (\Throwable $th) {
            //throw $th;
        } 
        return false;
    }
}