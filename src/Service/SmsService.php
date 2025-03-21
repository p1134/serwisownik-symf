<?php

namespace App\Service;

require __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;

class SmsService{
    private $sid;
    private $token;
    private $from;
    
    public function __construct(string $sid, string $token, string $from)
    {
        $this->sid = $sid;
        $this->token = $token;
        $this->from = $from;
    }

    public function sendSms(string $to, string $message)
    {
        $client = new Client($this->sid, $this->token);
        $client->messages->create(
            $to,
            [
                'from' => $this->from,
                'body' => $this->$message,
            ]
        );
    }
}