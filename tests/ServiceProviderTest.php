<?php

namespace DrillSergeant\Tests;

use DrillSergeant\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testEventsListenedTo()
    {
        $this->assertCount(1, app('events')->getListeners(JobProcessed::class));
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
