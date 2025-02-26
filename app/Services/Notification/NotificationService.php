<?php

namespace App\Services\Notification;

use App\Models\Token;
use Google\Client;

class NotificationService
{

    public function sendNotification($data, $tokens = null)
    {
        $credentialsFilePath = __DIR__ . "/../../../account_server.json";
        $client = new Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->setScopes('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $accessToken = $token['access_token'];

        $headers = [
          "Authorization: Bearer " . $accessToken,
          "Content-Type: application/json"
        ];

        foreach ($tokens as $token){
            $dataPost = [
                "message" => [
                    'token' => $token,
                    'data' => [
                        'url' => $data['url'] ?? "/",
                        'type' => $data['type'] ?? null,
                        'title' => $data['title'] ?? "Something",
                        'body' => $data['body'] ?? "Something",
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $data['title'] ?? "Something",
                                    'body' => $data['body'] ?? "Something",
                                ],
                                'content_available' => true,
                            ],
                            'android' => [
                                'priority' => 'high',
                            ],
//                            'webpush' => []
                        ]
                    ]
                ]
            ];

            $payload = json_encode($dataPost);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com//v1/{parent=projects/*}/messages:send");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $result = json_decode($response);

            if(isset($result->error->status) && $result->error->status == "NOT FOUND"){
                Token::where('token', $token)->delete();
            }

            curl_close($ch);
        }

    }

    static function sendNotificationRN($data, $tokens = null)
    {

        $headers = [
            "Content-Type: application/json"
        ];

        $payload = [
          'subIDs' => $tokens,
          'appId' => (int)env("NOTIFY_ID"),
          'appToken' => env("NOTIFY_TOKEN"),
          'title' => $data['title'] ?? "Something",
          'message' => $data['body'] ?? "Something",
          'pushData' => [
              'end_point' => $data['target_endpoint'] ?? "/",
          ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://app.nativenotify.com/api/indie/group/notification");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

    }
}
