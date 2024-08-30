<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class LogDataApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::get('https://randomuser.me/api/');
            if ($response->successful()) {
                $results = $response->json('results');
                Log::channel('result')->info('Random User Data:', $results);
            } else {
                Log::channel('result')->error('Failed to fetch random user data.', ['status' => $response->status()]);
            }
        } catch (\Exception $e) {
            Log::channel('result')->error('Error while fetching random user data: ' . $e->getMessage());
        }
    }
}
