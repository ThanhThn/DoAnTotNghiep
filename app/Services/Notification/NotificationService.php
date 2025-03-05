<?php

namespace App\Services\Notification;

use App\Events\NewNotification;
use App\Models\Notification;
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

    public function createNotification($data, $objectType, $objectId ,$tokens = null)
    {
        $data['title'] = $data['title'] ?? "Something";
        $data['body'] = $data['body'] ?? "Something";
        $data['target_endpoint'] = $data['target_endpoint'] ?? "/";

        $notification = Notification::create([
            'object_type' => $objectType,
            'object_id' => $objectId,
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'] ?? 'normal',
            'url' => $data['target_endpoint'],
        ]);

        event(new NewNotification($objectId, $objectType, $notification));
        $this->sendNotificationRN($data, $tokens);

        return $notification;
    }

    public function list($data)
    {
        $query = Notification::where([
            'object_id' => $data['object_id'],
            'object_type' => $data['object_type'],
        ])->orderBy('created_at', 'desc');

        $total = $query->count();

        $notifications = $query->offset($data['offset'] ?? 0)->limit($data['limit'] ?? 20)->get();

        return [
            'total' => $total,
            'data' => $notifications
        ];
    }
}
