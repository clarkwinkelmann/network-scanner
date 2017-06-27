<?php

namespace App\Console\Commands;

use App\Jobs\ArpScanJob;
use Illuminate\Console\Command;

class NetworkScanCommand extends Command
{
    protected $signature = 'network:scan';

    protected $description = 'Scan network with arp-scan';

    public function handle()
    {
        dispatch(new ArpScanJob());
    }
}
