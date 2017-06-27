<?php

namespace App\Jobs;

use App\ArpScan;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ArpScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $scan = explode("\n", shell_exec('arp-scan --localnet'));
        $time = Carbon::now();

        foreach ($scan as $line) {
            // Parse output lines
            $matches = [];
            if (preg_match('/^([0-9\.]+)\s+([0-9a-f:]+)\s+(.+)$/', $line, $matches) !== 1) {
                // Ignore lines that don't contain results
                continue;
            }

            ArpScan::create([
                'time' => $time,
                'ip'   => $matches[1],
                'mac'  => $matches[2],
                'info' => $matches[3],
            ]);
        }
    }
}
