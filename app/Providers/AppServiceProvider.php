<?php

namespace App\Providers;

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Flashcard;
use App\Policies\AnswerPolicy;
use App\Policies\AttemptPolicy;
use App\Policies\FlashcardPolicy;
use App\Repositories\FlashcardRepository;
use App\Repositories\FlashcardRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FlashcardRepositoryInterface::class, FlashcardRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Flashcard::class, FlashcardPolicy::class);
        Gate::policy(Answer::class, AnswerPolicy::class);
        Gate::policy(Attempt::class, AttemptPolicy::class);
    }
}
