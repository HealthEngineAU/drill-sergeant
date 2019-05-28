<?php

namespace DrillSergeant\Listeners;

use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;

class IncrementJobCount
{
    protected const CACHE_KEY_MONITORED_JOBS = 'monitored';
    protected const CACHE_KEY_LOCK = 'drillsergeant:lock';

    /** @var Repository */
    protected $cache;

    /** @var int */
    protected $lockTimeout;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache->store(config('drillsergeant.cache_store'));
        $this->lockTimeout = config('drillsergeant.cache_lock_timeout');
    }

    /**
     * Update the metrics cache with a new job increment.
     *
     * @var JobProcessed $event
     */
    public function handle(JobProcessed $event)
    {
        $job = $event->job;
        $cacheKey = $this->getCacheKey($job->getQueue(), $job->resolveName());

        // get the cache items storing which jobs are monitored and the current jobs counter
        $cacheItems = $this->cache->many([static::CACHE_KEY_MONITORED_JOBS, $cacheKey]);

        // if either the job counter is not set, or the current job is not in the list of monitored jobs, we need to
        // use a lock to update the respective one (or both)
        if ($this->cacheNeedsUpdatingUsingLock($cacheItems, $cacheKey)) {
            try {
                // update the cache key using an atomic lock
                $this->cache
                    ->lock(static::CACHE_KEY_LOCK)
                    ->block($this->lockTimeout, function () use ($cacheKey) {
                        // re-read the values inside the lock in case they changed
                        $cacheItems = $this->cache->many([static::CACHE_KEY_MONITORED_JOBS, $cacheKey]);

                        // update/set the counter
                        $cacheItems[$cacheKey] += 1;
                        // append this job to the list
                        $cacheItems[static::CACHE_KEY_MONITORED_JOBS] = collect($cacheItems[static::CACHE_KEY_MONITORED_JOBS])
                            ->push($cacheKey)
                            ->unique()
                            ->toArray();

                        // put values into cache
                        // NB: laravel has a bug in some cache drivers that means `forever()` can't be used
                        $this->cache->putMany($cacheItems, now()->addMinutes(5)->getTimestamp());
                    });
            } catch (Exception $exception) {
                Log::error('Failed to update metrics cache using atomic lock', ['exception' => $exception]);
            }
        } else {
            // otherwise we just need to increment the counter
            // (because current job is monitored and counter already exists)
            $this->cache->increment($cacheKey);
        }
    }

    protected function cacheNeedsUpdatingUsingLock($cacheItems, $jobCacheKey)
    {
        return !in_array($jobCacheKey, $cacheItems[static::CACHE_KEY_MONITORED_JOBS] ?? [])
            || $cacheItems[$jobCacheKey] === null;
    }

    protected function getCacheKey(string $queue, string $job)
    {
        return config('drillsergeant.cache_prefix') . ":${queue}:${job}";
    }
}
