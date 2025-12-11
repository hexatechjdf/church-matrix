<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlanningService;
use App\Models\CrmToken;
use App\Models\Headcount;
use Carbon\Carbon;

class SyncHeadcounts extends Command
{
    protected $signature = 'headcounts:sync {--created=} {--updated=}';

    protected $description = '';

    public function handle(PlanningService $service)
    {
        $createdDate = $this->option('created');
        $updatedDate = $this->option('updated');

        if (!$createdDate && !$updatedDate) {
            $this->error('Please provide created_at or updated_at');
            return Command::FAILURE;
        }

        $token = CrmToken::where('id', 5)->value('access_token');
        if (!$token) {
            $this->error('Token not found!');
            return Command::FAILURE;
        }

        $response = $service->getHeadcounts($createdDate, $updatedDate, $token);
        // dd($response);

        if (empty($response->data)) {
            $this->warn('No headcounts found!');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($response->data as $item) {
            Headcount::updateOrCreate(
                ['headcount_id' => $item->id],
                [
                    'total' => $item->attributes->total,
                    'headcount_created_at' => $item->attributes->created_at
                        ? Carbon::parse($item->attributes->created_at)->format('Y-m-d H:i:s')
                        : null,
                    'headcount_updated_at' => $item->attributes->updated_at
                        ? Carbon::parse($item->attributes->updated_at)->format('Y-m-d H:i:s')
                        : null,
                ]
            );
            $count++;
        }

        $this->info("Sync complete! {$count} headcounts saved.");
    }
}

    