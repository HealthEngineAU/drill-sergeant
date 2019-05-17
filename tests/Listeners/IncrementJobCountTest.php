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
            'drillsergeant.cache_store' => 'array',
            'drillsergeant.cache_prefix' => 'drillsergeant',
        ]);
    }

    public function testHandleJobEmptyCache()
    {
        // dispatch the event
        $job = new SyncJob(app(), json_encode(['job' => 'test']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));

        // assert the cache contains the correct keys
        $this->assertEquals(1, Cache::get('drillsergeant:sync:test'));
    }

    public function testHandleJobExistingCache()
    {
        // put an existing count into the cache
        Cache::put('drillsergeant:sync:test', 42, 9999);

        // dispatch the event
        $job = new SyncJob(app(), json_encode(['job' => 'test']), 'test-connection', null);
        event(new JobProcessed('test-connection', $job));

        // assert the cache contains the correct keys
        $this->assertEquals(43, Cache::get('drillsergeant:sync:test'));
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
