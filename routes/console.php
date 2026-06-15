<?php

use App\Console\Commands\CleanupExpiredImages;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CleanupExpiredImages::class)->everyFiveMinutes();