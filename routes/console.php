<?php

use App\Console\Commands\ResetDemo;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ResetDemo::class)->hourlyAt(0)->runInBackground();
