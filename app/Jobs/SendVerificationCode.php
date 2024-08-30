<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\SMSMessage;


class SendVerificationCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $code;

    /**
     * Create a new job instance.
     */
    public function __construct($phone, $code)
    {
        $this->phone = $phone;
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $smsMessage = new SMSMessage();
        $smsMessage->send($this->phone, $this->code);
    }
}
