<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SafraService;
use App\Models\AppUser;
use Carbon\Carbon;

class SyncSafraAppUsers extends Command
{
    protected $signature = 'safra:sync-app-users {--limit=2}';

    protected $description = 'Sync SAFRA members into app_users table';

    public function handle(SafraService $safra)
    {
        $this->info('Fetching SAFRA members...');

        $response = $safra->call(
            'sfrControlMember/GetBasicDetailInfoByModified',
            [
                'LastModifiedTime' => '1994-09-17T00:00:00',
                'Limit' => $this->option('limit'),
            ],
            'request'
        );

        $rows = $response->json();

        if (!isset($rows['member_list']) || empty($rows['member_list'])) {
            $this->error('No SAFRA data found');
            return Command::FAILURE;
        }

        $count = 0;

        foreach ($rows['member_list'] as $data) {

            // DOB
            $dob = null;
            if (!empty($data['BirthMonth']) && !empty($data['BirthYear'])) {
                $dob = Carbon::create(
                    $data['BirthYear'],
                    $data['BirthMonth'],
                    1
                )->format('Y-m-d');
            }

            AppUser::updateOrCreate(
                ['token' => $data['Token'] ?? null],   // UNIQUE KEY
                [
                    'expiry_date'          => $data['SafraMembershipExpiry'] ?? null,
                    'email'                => $data['PrimaryContactEmail'] ?? null,
                    'phone_number'         => $data['PrimaryContactMobile'] ?? null,
                    'gender'               => $data['Gender'] ?? null,
                    'date_of_birth'        => $dob,
                    'is_vip'               => $data['isVIP'] ?? false,
                    'member_category'      => $data['MemberCategory'] ?? null,
                    'member_id'            => $data['MemberId'] ?? null,
                    'membership_type_code' => $data['MembershipTypeCode'] ?? null,
                    'modified_date_time'   => $data['ModifiedDateTime'] ?? null,
                    'nric'                 => $data['Nric'] ?? null,
                    'membership_status'    => $data['SafraMembershipStatus'] ?? null,
                ]
            );

            $count++;
        }

        $this->info("SAFRA sync completed. {$count} users synced.");

        return Command::SUCCESS;
    }
}
