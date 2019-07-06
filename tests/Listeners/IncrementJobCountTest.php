<?php

namespace DrillSergeant\Tests;

use DrillSergeant\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;

class IncrementJobCountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // configure an array cache to test with
        config([
            'drillsergeant.cache_store' => 'redis',
            'drillsergeant.cache_prefix' => 'drillsergeant',
        ]);

        Cache::setDefaultDriver('redis');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Cache::store('redis')->flush();
    }

    public function testHandleJobEmptyCache()
    {
        // put an existing count into the cache
        Cache::put('monitored', ['drillsergeant:sync:test'], 9999);

        // dispatch the event
        $job = new SyncJob(app(), json_encode(['job' => 'test']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));

        // assert the cache contains the correct values
        $this->assertEquals(1, Cache::get('drillsergeant:sync:test'));
        $this->assertEquals(['drillsergeant:sync:test'], Cache::get('monitored'));
    }

    public function testHandleJobExistingCache()
    {
        // put an existing count into the cache
        Cache::put('drillsergeant:sync:test', 42, 9999);
        Cache::put('monitored', ['drillsergeant:sync:test'], 9999);

        // dispatch the event
        $job = new SyncJob(app(), json_encode(['job' => 'test']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));

        // assert the cache contains the correct keys
        $this->assertEquals(43, Cache::get('drillsergeant:sync:test'));
        $this->assertEquals(['drillsergeant:sync:test'], Cache::get('monitored'));
    }

    public function testHandleJobEmptyCacheNoMonitoredMetrics()
    {
        // dispatch the event
        $job = new SyncJob(app(), json_encode(['job' => 'test']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));

        // assert the cache contains the correct keys
        $this->assertEquals(1, Cache::get('drillsergeant:sync:test'));
        $this->assertEquals(['drillsergeant:sync:test'], Cache::get('monitored'));
    }

    public function testHandleJobExistingCacheNoMonitoredMetrics()
    {
        // put an existing count into the cache
        Cache::put('drillsergeant:sync:test', 42, 9999);

        // dispatch the event
        $job = new SyncJob(app(), json_encode(['job' => 'test']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));

        // assert the cache contains the correct keys
        $this->assertEquals(43, Cache::get('drillsergeant:sync:test'));
        $this->assertEquals(['drillsergeant:sync:test'], Cache::get('monitored'));
    }

    public function testMultipleJobs()
    {
        // put an existing count into the cache
        Cache::put('drillsergeant:sync:test', 42, 9999);

        // dispatch the event
        $job = new SyncJob(app(), json_encode(['job' => 'test']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));
        $job = new SyncJob(app(), json_encode(['job' => 'test2']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));

        // assert the cache contains the correct keys
        $this->assertEquals(43, Cache::get('drillsergeant:sync:test'));
        $this->assertEquals(1, Cache::get('drillsergeant:sync:test2'));
        $this->assertEquals(['drillsergeant:sync:test', 'drillsergeant:sync:test2'], Cache::get('monitored'));
    }

    public function testLockAlreadyAcquired()
    {
        // set the timeout to 0 so we don't block long tests
        config(['drillsergeant.cache_lock_timeout' => 0]);
        // put a lock in place to simulate another process having the lock
        Cache::lock('drillsergeant:lock', 15)->acquire();

        // dispatch the event
        $job = new SyncJob(app(), json_encode(['job' => 'test']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));

        // assert the cache was not updated
        $this->assertNull(Cache::get('drillsergeant:sync:test'));
        $this->assertNull(Cache::get('monitored'));
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
