<?php

namespace App\Console\Commands;

use App\Http\Controllers\CloudflareController;
use Illuminate\Console\Command;

class UpdateCloudflareARecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:update-a-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update A records for all domains in Cloudflare to the new IP';

    /**
     * Execute the console command.
     */
    public function handle( CloudflareController $cloudflare)
    {
        $ip = $this->ask('Enter New IP Address');
        $results = $cloudflare->updateAllARecords($ip);

        foreach ($results as $result) {
            $status = $result['success'] ? 'success' : 'failure';
            $this->info("{$status} : {$result['domain']} - {$result['record']} => {$result['message']}");
        }
    }
}
