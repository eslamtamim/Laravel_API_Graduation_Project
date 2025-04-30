<?php
namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FCMService
{
    private $firebase;

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(storage_path('app/handsmen-7c4bd-firebase-adminsdk-fbsvc-03ddc3fb62.json'))
            ->createMessaging();
    }

    public function sendNotification(array $message)
    {
        $response = $this->firebase->send($message);
        return $response;
    }
}
