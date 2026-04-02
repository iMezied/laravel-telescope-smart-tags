<?php

namespace Mezied\TelescopeSmartTags\Tests;

use Laravel\Telescope\IncomingEntry;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Laravel\Telescope\TelescopeServiceProvider::class,
            \Mezied\TelescopeSmartTags\TelescopeSmartTagsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('telescope.enabled', true);
        $app['config']->set('telescope.driver', 'database');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Build a minimal IncomingEntry for testing.
     */
    protected function makeEntry(string $type, array $content = []): IncomingEntry
    {
        return IncomingEntry::make($content)->type($type);
    }

    protected function makeRequestEntry(array $content = []): IncomingEntry
    {
        return $this->makeEntry('request', array_merge([
            'uri'             => '/api/test',
            'method'          => 'GET',
            'response_status' => 200,
            'duration'        => 100,
        ], $content));
    }

    protected function makeExceptionEntry(array $content = []): IncomingEntry
    {
        return $this->makeEntry('exception', array_merge([
            'class'   => 'RuntimeException',
            'message' => 'Test exception',
            'file'    => '/app/test.php',
            'line'    => 42,
        ], $content));
    }

    protected function makeQueryEntry(array $content = []): IncomingEntry
    {
        return $this->makeEntry('query', array_merge([
            'sql'  => 'select * from users',
            'time' => 100,
        ], $content));
    }
}
