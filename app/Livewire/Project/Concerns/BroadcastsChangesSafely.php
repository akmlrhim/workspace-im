<?php

namespace App\Livewire\Project\Concerns;

use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Log;

/**
 * Guards user actions against broadcaster outages.
 *
 * The project events broadcast synchronously, which puts an HTTP round trip to
 * Pusher inside the user's request. When Pusher is slow or unreachable that
 * request used to stall for the full connect timeout and then fail with a 500 —
 * losing the user's edit even though it had already been written to the
 * database. Real-time delivery is a nice-to-have; the write is not, so a
 * broadcast failure is logged and swallowed instead of surfacing.
 */
trait BroadcastsChangesSafely
{
    /**
     * Dispatch a broadcast event, degrading to no real-time update on failure.
     *
     * @param  \Closure(): void  $dispatch
     */
    private function broadcastSafely(\Closure $dispatch): void
    {
        try {
            $dispatch();
        } catch (BroadcastException $e) {
            Log::warning('Broadcast gagal dikirim; perubahan tetap tersimpan.', [
                'component' => static::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
