<?php

namespace App\Jobs\Setting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Jobs\Setting\CreateTokenJob;

class ManageTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $chunks;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chunks = [])
    {
        $this->chunks = $chunks;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $res = $this->chunks;
        $userIds = $res->keys()->all();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        foreach ($res as $key => $data) {
            $u = $users->get($key);
            if (!$u) continue;

            $crm_type = null;
            $payload = [];

            if (!empty(@$data['planning_client_id'])) {
                $crm_type = 'planning';
                $payload = [
                    'access_token' => @$data['planning_client_id'],
                    'refresh_token' => @$data['planning_client_sceret'] ?? '',
                    'user_id' => $key,
                    'location_id' => $u->location,
                    'company_id' => @$data['planning_organization_id'] ?? null,
                    'crm_type' => $crm_type,
                    'organization_name' => @$data['planning_organization_name'] ?? null,
                    'user_type' => $u->role == 0 ? 'Company' : 'Location',
                ];

                if (!empty(@$data['workflow_selected'])) {
                    $u->workflow_selected = @$data['workflow_selected'];
                    $u->save();
                }
            }

            if (!empty(@$data['ghl_access_token'])) {
                $crm_type = 'ghl';
                $payload = [
                    'access_token' => @$data['ghl_access_token'] ?? '',
                    'refresh_token' => @$data['ghl_refresh_token'] ?? '',
                    'user_id' => $key,
                    'location_id' => $u->location ?? null,
                    'company_id' => @$data['ghl_company_id'] ?? null,
                    'crm_type' => $crm_type,
                    'user_type' => $u->role == 0 ? 'Company' : 'Location',
                ];
            }

            if ($crm_type) {
                CreateTokenJob::dispatch($payload);
            }
        }
    }
}
