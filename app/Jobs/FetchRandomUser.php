<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchRandomUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Make the HTTP request
        $response = Http::get('https://randomuser.me/api/');

        // Log the results object if the request was successful
        if ($response->successful()) {
            $data = $response->json()['results'] ?? [];
            Log::info('Random User Data:', $data);
        } else {
            Log::error('Failed to fetch random user data');
        }
    }
}
