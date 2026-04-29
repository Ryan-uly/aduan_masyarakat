<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Complaint;
use App\Policies\ComplaintPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    protected $policies = [
        Complaint::class => ComplaintPolicy::class,
    ];
}
