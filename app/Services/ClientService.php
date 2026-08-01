<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\User;
use App\Setting;

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

    public function sendEmail($recipients = [], $subject = "", $content = "", $file = "", $encode = "url")
    {
        $url = config('mail.email_server').'/send';
        $apiKey = config('mail.email_server_key');

        $header = [
            'apikey' => $apiKey
        ];

        $payload = [
            "recipients" => $recipients,
            "subject" => $subject,
            "encode" => $encode,
            "content" => $content,
            "file" => $file
        ];

        return $this->request('post', $url, 'json', $header, $payload);
    }

    public function getEmailByRoleAndSiteCode($roles = [], $site_code = null)
    {
        $query = User::whereIn('account_role', $roles);

        if ($site_code) {
            $query->where('site_code', $site_code);
        }

        return $query->pluck('email')->merge($query->pluck('second_email'))->unique()->filter(fn ($val) => $val)->toArray();
    }

    public function sendNotification($recipients = [], $subject = "", $content = "")
    {
        $this->sendEmail($recipients, $subject, $content);
    }

    public function getEmailCc()
    {
        return Setting::where('key', 'email_cc')->pluck('value')->toArray();
    }
}
