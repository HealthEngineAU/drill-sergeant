<?php

namespace DrillSergeant;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Queue\Events\JobProcessed;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/drillsergeant.php' => config_path('drillsergeant.php')]);

        Event::listen(JobProcessed::class, Listeners\IncrementJobCount::class);
    }
}
