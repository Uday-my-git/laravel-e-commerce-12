<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
    */
    protected $signature = 'test:cron';
    
    protected $description = 'Command description'; 

    /**
     * The console command description.
     *
     * @var string
    */
    /**
     * Execute the console command.
    */

    public function handle()
    {
        $userData = \DB::table('users')->find(110);

        if (empty($userData)) {
            Log::warning('User not found for Cron Job.', [
                'userId' => 110,
                'time' => now(),
                'pid' => getmypid(),
            ]);
            return;
        }

        $totalCount = \DB::table('users')->count();

        Log::info('Cron Job running Successfull.', [
            'userData'       => $userData->name,
            'totalUserCount' => $totalCount,
            'time'           => now(),
            'pid'            => getmypid(),
        ]);

        // info(json_encode($userData) . "Cron Job running at ". now());
    }
}
