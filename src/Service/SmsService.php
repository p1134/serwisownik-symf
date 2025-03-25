<?php

namespace App\Service;

// require __DIR__ . '/vendor/autoload.php';
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
        try{
            $message = $client->messages->create("+48".
            $to,
            [
                'from' => $this->from,
                'body' => $message,
                ]
            );
            return $message->sid;
        } catch(\Exception $e) {
            return 'Error: '.$e->getMessage();
        }

    }
}