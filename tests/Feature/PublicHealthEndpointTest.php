<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicHealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_health_endpoint_returns_ok_without_authentication(): void
    {
        $this->get(route('health.basic'))
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['status', 'service', 'timestamp']);
    }

    public function test_readiness_endpoint_returns_ready_when_database_and_queue_tables_are_available(): void
    {
        $this->get(route('health.ready'))
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => ['database', 'jobs_table', 'failed_jobs_table'],
            ]);
    }

    public function test_ops_health_endpoint_returns_not_found_when_token_not_configured(): void
    {
        config()->set('services.health.token', '');

        $this->get(route('health.ops'))
            ->assertNotFound();
    }

    public function test_ops_health_endpoint_requires_valid_token_header(): void
    {
        config()->set('services.health.token', 'ops-secret-token');

        $this->get(route('health.ops'))
            ->assertStatus(401)
            ->assertJsonPath('status', 'unauthorized');

        $this->withHeaders(['X-Health-Token' => 'wrong-token'])
            ->get(route('health.ops'))
            ->assertStatus(401);
    }

    public function test_ops_health_endpoint_returns_metrics_with_valid_token(): void
    {
        config()->set('services.health.token', 'ops-secret-token');

        Cache::put('system.scheduler_heartbeat_at', now()->subMinute()->toIso8601String());

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => '{"job":"queued"}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{"job":"failed"}',
            'exception' => 'Example failure',
            'failed_at' => now(),
        ]);

        $this->withHeaders(['X-Health-Token' => 'ops-secret-token'])
            ->get(route('health.ops'))
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure([
                'status',
                'timestamp',
                'queue' => ['connection', 'pending_jobs', 'failed_jobs'],
                'scheduler' => ['heartbeat_at', 'heartbeat_age_minutes'],
            ]);
    }

    public function test_ready_ops_endpoint_requires_token_and_returns_not_found_when_not_configured(): void
    {
        config()->set('services.health.token', '');

        $this->get(route('health.ready.ops'))
            ->assertNotFound();

        config()->set('services.health.token', 'ops-secret-token');

        $this->get(route('health.ready.ops'))
            ->assertStatus(401)
            ->assertJsonPath('status', 'unauthorized');
    }

    public function test_ready_ops_endpoint_returns_latency_metrics_with_valid_token(): void
    {
        config()->set('services.health.token', 'ops-secret-token');

        $this->withHeaders(['X-Health-Token' => 'ops-secret-token'])
            ->get(route('health.ready.ops'))
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    'database' => ['status', 'latency_ms'],
                    'jobs_table' => ['status', 'latency_ms'],
                    'failed_jobs_table' => ['status', 'latency_ms'],
                ],
            ]);
    }
}
