<?php

namespace App\Providers;

use App\Models\Answer;
use App\Models\Attempt;
use App\Policies\AnswerPolicy;
use App\Policies\AttemptPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Answer::class, AnswerPolicy::class);
        Gate::policy(Attempt::class, AttemptPolicy::class);
    }
}
