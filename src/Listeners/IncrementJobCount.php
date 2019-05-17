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
        $job = $event->job;

        if (!$cache->has($this->getCacheKey($job->getQueue(), $job->resolveName()))) {
            // laravel has a bug in some cache drivers that means `forever()` can't be used
            $cache->put($this->getCacheKey($job->getQueue(), $job->resolveName()), 1, now()->addYears(5)->getTimestamp());
        } else {
            $cache->increment($this->getCacheKey($job->getQueue(), $job->resolveName()));
        }
    }

    protected function getCacheKey(string $queue, string $job)
    {
        return config('drillsergeant.cache_prefix') . ":${queue}:${job}";
    }
}
