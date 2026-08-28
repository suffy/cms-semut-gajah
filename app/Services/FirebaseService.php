<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class FirebaseService
{
    protected string $projectId;
    protected string $serviceAccount;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');

        $this->serviceAccount = base_path(
            config('firebase.service_account')
        );
    }

    protected function getAccessToken(): string
    {
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $this->serviceAccount
        );

        $token = $credentials->fetchAuthToken();

        if (!isset($token['access_token'])) {
            throw new \Exception('Unable to get Firebase access token');
        }

        return $token['access_token'];
    }

    public function send(
        string $deviceToken,
        string $title,
        string $body,
        array $data = []
    ) {
        $accessToken = $this->getAccessToken();

        $url = sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            $this->projectId
        );

        $payload = [
            'message' => [
                'token' => $deviceToken,

                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],

                'data' => collect($data)->map(
                    fn ($value) => is_string($value)
                        ? $value
                        : json_encode($value)
                )->toArray(),

                'android' => [
                    'priority' => 'HIGH',
                ],
            ],
        ];

        return Http::withToken($accessToken)
            ->acceptJson()
            ->post($url, $payload)
            ->throw()
            ->json();
    }
}
