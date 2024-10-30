<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\DeleteOldTrashedPosts;
use App\Jobs\FetchRandomUser;
use Illuminate\Support\Facades\Schedule;
// Schedule to run DeleteOldTrashedPosts job daily
// Schedule::job(new DeleteOldTrashedPosts)->daily()->timezone('UTC')->description('Delete old trashed posts');
Schedule::call(new DeleteOldTrashedPosts)->daily();

// Schedule to run FetchRandomUser job every six hours
Schedule::job(new FetchRandomUser)->everySixHours()->timezone('UTC')->description('Fetch random user data');

// You can keep the inspire command if you want to test console commands
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
