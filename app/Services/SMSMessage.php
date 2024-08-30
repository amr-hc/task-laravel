<?php

namespace App\Services;

use Log;
use \Vonage\SMS\Message\SMS;
use \Vonage\Client\Credentials\Basic;
use \Vonage\Client;

class SMSMessage
{
    public $basic;
    public $client;

    public function __construct()
    {
        $this->basic = new Basic(env('VONAGE_API_KEY'), env('VONAGE_API_SECRET'));
        $this->client = new Client($this->basic);
    }

    public function send($phone, $code)
    {

        $response = $this->client->sms()->send(
            new SMS("2" . $phone, "Develop Network", "Your Code to activate your account is " . $code)
        );
        
        $message = $response->current();
        
        if ($message->getStatus() != 0) {
            Log::error( $message->getStatus());
        }
    }
}
