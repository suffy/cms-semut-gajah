<?php

namespace App\Services;

use GuzzleHttp\Client;

class ClientService
{
    public function request($method, $url, $type, $header = null, $data = null)
    {
        $client = new Client();
        $res = $client->request(
            $method,
            $url,
            [
                'headers' => $header,
                $type => $data
            ]
        );

        return json_decode($res->getBody(), true);
    }
}