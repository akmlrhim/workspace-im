<?php

namespace App\Livewire\Concerns;

use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Log;

trait BroadcastsChangesSafely
{
    /**
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
