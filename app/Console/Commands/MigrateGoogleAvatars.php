<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MigrateGoogleAvatars extends Command
{
    protected $signature = 'avatars:migrate-google';

    protected $description = 'Download Google avatar URLs and store them locally';

    public function handle(): int
    {
        $users = User::whereNotNull('google_id')
            ->where('avatar', 'like', '%googleusercontent.com%')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users with external Google avatar URLs found.');

            return self::SUCCESS;
        }

        $this->info("Migrating {$users->count()} Google avatar(s)...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($users as $user) {
            $url = preg_replace('/=s\d+-c$/', '=s400-c', $user->avatar);

            try {
                $response = Http::timeout(15)->get($url);

                if ($response->successful()) {
                    $filename = 'avatars/google_'.$user->google_id.'.jpg';
                    Storage::disk('public')->put($filename, $response->body());
                    $user->update(['avatar' => Storage::disk('public')->url($filename)]);
                    $success++;
                } else {
                    $user->update(['avatar' => null]);
                    $failed++;
                }
            } catch (\Exception) {
                $user->update(['avatar' => null]);
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Success: {$success}, Failed (cleared): {$failed}.");

        return self::SUCCESS;
    }
}
