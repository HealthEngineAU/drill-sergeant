<?php

namespace DrillSergeant\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\Events\JobProcessed;

class IncrementJobCount
{
    /**
     * Update the metrics cache with a new job increment.
     *
     * @var JobProcessed $event
     */
    public function handle(JobProcessed $event)
    {
        $cache = Cache::store(config('drillsergeant.cache_store'));

        if (!$cache->has($this->getCacheKey($event->job->getQueue()))) {
            // laravel has a bug in some cache drivers that means `forever()` can't be used
            $cache->put($this->getCacheKey($event->job->getQueue()), 1, now()->addYears(5)->getTimestamp());
        } else {
            $cache->increment($this->getCacheKey($event->job->getQueue()));
        }
    }

    protected function getCacheKey(string $name)
    {
        return config('drillsergeant.cache_prefix') . ":${name}";
    }
}
