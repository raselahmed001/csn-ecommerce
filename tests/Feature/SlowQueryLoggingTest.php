<?php

namespace Tests\Feature;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SlowQueryLoggingTest extends TestCase
{
    public function test_it_logs_a_warning_for_a_query_slower_than_500_ms(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn ($message, $context) => $message === 'Slow database query detected'
                && $context['sql'] === 'select 1'
                && $context['time'] === 600.0);

        event(new QueryExecuted('select 1', [], 600.0, DB::connection()));
    }

    public function test_it_does_not_log_a_fast_query(): void
    {
        Log::shouldReceive('warning')->never();

        event(new QueryExecuted('select 1', [], 10.0, DB::connection()));
    }
}
