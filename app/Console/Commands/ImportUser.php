<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Models\KeyPassbookCredit;
use App\Models\Tier;
use App\Models\UserReferral;
use App\Models\UserTier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 
        $delimiter = ','; 
        $filePath = public_path('report') . '/userkey2.csv';

        //
        $header = null;
        $i = 0;
        $tier = Tier::orderBy('t_order', 'ASC')->first();
        $reff = [];

        if (($handle = fopen($filePath, 'r')) !== false) {

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

            try {
                //code...

                if ($i == 0) {
                    $header = $row;
                    Log::info('new header ' . json_encode($header));
                } else {
                    $app = AppUser::where('unique_id', $row[1])->first();
                    if ($app) {
                        //if ($app->available_key != $row[1]) {
                            $app->available_key = $row[2];
                            Log::info("OLD Key " . $app->available_key . " new key" . $row[2]);
                            KeyPassbookCredit::where('user_id', $app->id)->update([
                                'no_of_key' => $row[2],
                                'remain_keys' => $row[2]
                            ]);
                            $app->save();
                      //  }
                    }
                }
                $i++;
            } catch (\Throwable $th) {
                  throw $th;
                Log::info("Error creating", $row);
            }
        }
    }
        Log::info("Code");

        return ;




        if (($handle = fopen($filePath, 'r')) !== false) {




            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

                try {
                    //code...

                    if ($i == 0) {
                        $header = $row;
                        Log::info('new header ' . json_encode($header));
                    } else {
                        // Start Importing
                        $data =  AppUser::create([
                            'unique_id' => Str::upper($row[0]),
                            'name' => $row[1],
                            'gender' => strtolower($row[2]) == 'male' ? 'Male' : 'Female',
                            'email' => $row[3],
                            'date_of_birth' => Carbon::parse($row[4]),
                            'expiry_date' => Carbon::parse($row[5]),
                            'status' => $this->statusChange($row[6]),
                            'blacklist_reason' => $row[7],
                            'company_id' => $row[8],
                            'c_name' => $row[9],
                            'phone_number' =>  substr($row[10], 2),
                            'my_code' => Str::upper($row[11]),
                            'password' => Hash::make('P@ssword@1011'),
                            'user_type' => $row[13] == 1 ? 'Airport Pass Holder' : 'Aircrew',
                            'country_code' => '+65',
                            'referral_code' => $row[12],
                            'sms_subscription' => 1,
                            'email_subscription' => 1,
                            'whatsapp_subscription' => 1,
                            'verified_at' => $row[7] == 5 ? null  : Carbon::now(),
                            'c_code' => '',
                            'aircrew_unique' => '',
                            'push_system_noti' => 1,
                            'push_promotion_noti' => 1,
                            'push_other_noti' => 1,
                            'sms_noti' => 1,
                            'email_noti' => 1,
                            'last_login' => Carbon::now(),
                            'noti_count' => 0,
                            'otp' => generateNumericOTP(6),
                        ]);

                        if ($row[14] > 0) {

                            KeyPassbookCredit::create([
                                'user_id' => $data->id,
                                'no_of_key' => (int)$row[14],
                                'remain_keys' => (int)$row[14],
                                'earn_way' => 'admin_credit',
                                'meta_data' => 'Keys Adjustment by system',
                                'expiry_date' => keyExpiryDate()
                            ]);
                        }

                        if ($row[12]) {

                            $reff[] = [
                                'referral_to' => $data->id,
                                'by' => $row[12],
                            ];
                            Log::info("REF", $reff);
                        }

                        UserTier::create([
                            'user_id' => $data->id,
                            'tier_id' => $tier->id,
                            'end_at' => tireExpiryDate(),
                            'status' => "Active",
                        ]);
                    }

                    $i++;
                } catch (\Throwable $th) {
                    //   throw $th;
                    Log::info("Error creating", $row);
                }
            }


            // assign the user Rafael data
            foreach ($reff as $value) {

                $rby =   AppUser::where('my_code', $value['by'])->first()->id;
                UserReferral::create([
                    'referral_by' => $rby,
                    'referral_to' => $value['referral_to'],
                    'status' => 'Completed'
                ]);
            }
        }
        Log::info('Executing');
    }
    public function statusChange($status)
    {
        // enum('Active','Inactive','Blacklist','Expired','Awaiting Activation')	

        $sString = "Awaiting Activation";

        switch ($status) {
            case '1':
                $sString = "Active";

                break;

            case '2':
                $sString = "Inactive";

                break;
            case '3':
                $sString = "Blacklist";

                break;
            case '4':
                $sString = "Expired";

                break;
            case '5':
                $sString = "Awaiting Activation";

                break;

            default:
                $sString = "Awaiting Activation";


                break;
        }
        return $sString;
    }
}
