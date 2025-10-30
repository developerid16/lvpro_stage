<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Mail\APHExpiry;
use App\Mail\KeyExpiry;
use App\Models\AppUser;
use App\Mail\RewardExpiredMail;
use Illuminate\Console\Command;
use App\Models\ContentManagement;
use App\Models\InAppNoti;
use App\Models\KeyPassbookCredit;
use Illuminate\Support\Facades\DB;
use App\Models\UserPurchasedReward;
use Illuminate\Support\Facades\Mail;

class ExpiredNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expired:notification';

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



        $days = ["", "_two"];
        foreach ($days as $key => $value) {

            $setting = ContentManagement::whereIn('name', [
                "email_aph_expiry_noti_day$value",
                "email_reward_expiry_noti_day$value",
                "email_keys_expiry_noti_day$value",
                "keys_expiry_noti_day$value",
                "reward_expiry_noti_day$value",
                "aph_expiry_noti_day$value",
                "push_aph_expiry_noti_day$value",
                "push_keys_expiry_noti_day$value",
                "push_reward_expiry_noti_day$value",
            ])->where('value', '>', 0)->pluck('value', 'name');
            \Log::info("Executing command " .  Carbon::today()->addDays($setting["email_aph_expiry_noti_day$value"]));
            \Log::info($setting);

            //
            // email first chance to run the

            // $emailrewaerdDays = isset($setting["email_reward_expiry_noti_day$value"]) ? $setting["email_reward_expiry_noti_day$value"] : 0;
            // $emailkeyDays = isset($setting["email_keys_expiry_noti_day$value"]) ? $setting["email_keys_expiry_noti_day$value"] : 0;
            // $emailaphdDays = isset($setting["email_aph_expiry_noti_day$value"]) ? $setting["email_aph_expiry_noti_day$value"] : 0;





            $emailrewaerdDaysPush = isset($setting["push_reward_expiry_noti_day$value"]) ? $setting["push_reward_expiry_noti_day$value"] : 0;
            $emailkeyDaysPush = isset($setting["push_keys_expiry_noti_day$value"]) ? $setting["push_keys_expiry_noti_day$value"] : 0;
            $emailaphdDaysPush = isset($setting["push_aph_expiry_noti_day$value"]) ? $setting["push_aph_expiry_noti_day$value"] : 0;






            $pushNoti = [
                "reward" => [
                    'title' => 'Rewards Expiry Reminder',
                    'sub_title' => "Your rewards are expiring in $emailrewaerdDaysPush days. Login to view your rewards now!",
                ],
                "aph" => [
                    'title' => 'Your Airport Pass is expiring soon!',
                    'sub_title' => 'Remember to update your new Airport Pass ID & expiry date to retain your account and Keys earned!',
                ],
                "key" => [
                    'title' => "Your Keys are expiring in $emailkeyDaysPush days!",
                    'sub_title' => 'Check your available Keys for rewards redemption before it expires.',
                ]
            ];


            // PUSH NOTIFICATION CODE

            if ($emailaphdDaysPush > 0) {
                $aphEmail = AppUser::whereIn('status', ['Active', 'Inactive'])->where('user_type', 'Airport Pass Holder')->whereDate('expiry_date', Carbon::today()->addDays($emailaphdDaysPush))->get();
                foreach ($aphEmail as $user) {
                    if ($user->push_system_noti) {
                        $tokes = $user->deviceTokens->pluck('token')->toArray();
                        if (count($tokes)  > 0) {
                            sendNotification($pushNoti['aph']['sub_title'], $pushNoti['aph']['title'], $tokes);
                        }
                    }
                    InAppNoti::create([
                        'title' => $pushNoti['aph']['title'],
                        'content' => $pushNoti['aph']['sub_title'],
                        'type' => "System",
                        'user_id' => $user->id
                    ]);
                }
            }
            if ($emailrewaerdDaysPush > 0) {
                $userPurchasesReward = UserPurchasedReward::with(['user:id,name,email,email_noti,status' => function ($query) {
                    $query->whereIn('status', ['Active', 'Inactive']);
                }])->whereHas('user')->where('status', 'Purchased')->whereDate('expiry_date', Carbon::today()->addDays($emailrewaerdDaysPush))->get();

                foreach ($userPurchasesReward as $reward) {

                    if ($reward->user->push_system_noti) {
                        $tokes = $reward->user->deviceTokens->pluck('token')->toArray();
                        if (count($tokes)  > 0) {
                            sendNotification($pushNoti['reward']['sub_title'], $pushNoti['reward']['title'], $tokes);
                        }
                    }
                    InAppNoti::create([
                        'title' => $pushNoti['reward']['title'],
                        'content' => $pushNoti['reward']['sub_title'],
                        'type' => "System",
                        'user_id' => $reward->user->id
                    ]);
                }
            }

            if ($emailkeyDaysPush > 0) {
                $keys  = KeyPassbookCredit::with(['user' => function ($query) {
                    $query->whereIn('status', ['Active', 'Inactive']);
                }])->whereHas('user')->select('*', DB::raw('count(*) as count'), DB::raw('sum(remain_keys) as rk'))->where([['remain_keys', '>', 0]])->whereDate('expiry_date', Carbon::today()->addDays($emailkeyDaysPush))->groupBy('user_id')->get();
                foreach ($keys as $key) {

                    if ($key->user->push_system_noti) {
                        $tokes = $key->user->deviceTokens->pluck('token')->toArray();
                        if (count($tokes)  > 0) {
                            sendNotification($pushNoti['key']['sub_title'], $pushNoti['key']['title'], $tokes);
                        }
                    }
                    InAppNoti::create([
                        'title' => $pushNoti['key']['title'],
                        'content' => $pushNoti['key']['sub_title'],
                        'type' => "System",
                        'user_id' => $key->user->id
                    ]);
                }
            }

            // END PUSH NOTIFICATION



            if (isset($setting["email_aph_expiry_noti_day$value"])) {

                $aphEmail = AppUser::whereIn('status', ['Active', 'Inactive'])->where('user_type', 'Airport Pass Holder')->whereDate('expiry_date', Carbon::today()->addDays($setting["email_aph_expiry_noti_day$value"]))->get();
                foreach ($aphEmail as $user) {
                    if ($user->email_noti) {

                        $emailData = $user;
                        $day = $setting["email_aph_expiry_noti_day$value"];
                        $emailData['text'] = "Your account is expiring in $day days. Please update your Airport Pass ID and expiry date on the app to retain your account and Keys earned.";
                        $emailData['provider']  = substr($user->email, strpos($user->email, '@') + 1);

                        try {
                            \Log::info(" $value Email send for APH " . $user->email . $emailData['text']);
                            Mail::to($user->email)->send(
                                new APHExpiry($emailData)
                            );
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                    // if ($user->push_system_noti) {
                    //     $tokes = $user->deviceTokens->pluck('token')->toArray();
                    //     if (count($tokes)  > 0) {
                    //         sendNotification($pushNoti['aph']['sub_title'], $pushNoti['aph']['title'], $tokes);
                    //     }
                    // }
                    // InAppNoti::create([
                    //     'title' => $pushNoti['aph']['title'],
                    //     'content' => $pushNoti['aph']['sub_title'],
                    //     'type' => "System",
                    //     'user_id' => $user->id
                    // ]);
                }
            }

            if (isset($setting["email_reward_expiry_noti_day$value"])) {

                $userPurchasesReward = UserPurchasedReward::with(['user:id,name,email,email_noti,status' => function ($query) {
                    $query->whereIn('status', ['Active', 'Inactive']);
                }])->whereHas('user')->where('status', 'Purchased')->whereDate('expiry_date', Carbon::today()->addDays($setting["email_reward_expiry_noti_day$value"]))->get();
                \Log::info($userPurchasesReward);
                foreach ($userPurchasesReward as $reward) {
                    if ($reward->user->email_noti) {

                        $day = $setting["email_reward_expiry_noti_day$value"];
                        $keyData['text'] = "Your rewards are expiring in  $day  days. Login to view your rewards now!";
                        $keyData['provider']  = substr($reward->user->email, strpos($reward->user->email, '@') + 1);

                        try {
                            \Log::info("$day Email reward " . $user->email);

                            Mail::to($reward->user->email)->send(
                                new RewardExpiredMail($keyData)
                            );
                        } catch (\Throwable $th) {
                            throw $th;
                        }
                    }
                    // if ($reward->user->push_system_noti) {
                    //     $tokes = $reward->user->deviceTokens->pluck('token')->toArray();
                    //     if (count($tokes)  > 0) {
                    //         sendNotification($pushNoti['reward']['sub_title'], $pushNoti['reward']['title'], $tokes);
                    //     }
                    // }
                    // InAppNoti::create([
                    //     'title' => $pushNoti['reward']['title'],
                    //     'content' => $pushNoti['reward']['sub_title'],
                    //     'type' => "System",
                    //     'user_id' => $reward->user->id
                    // ]);
                }
            }
            if (isset($setting["email_keys_expiry_noti_day$value"])) {


                $keys  = KeyPassbookCredit::with(['user' => function ($query) {
                    $query->whereIn('status', ['Active', 'Inactive']);
                }])->whereHas('user')->select('*', DB::raw('count(*) as count'), DB::raw('sum(remain_keys) as rk'))->where([['remain_keys', '>', 0]])->whereDate('expiry_date', Carbon::today()->addDays($setting["email_keys_expiry_noti_day$value"]))->groupBy('user_id')->get();
                foreach ($keys as $key) {
                    if ($key->user->email_noti) {
                        $ek = $key['rk'];
                        $bk = $key->user->available_key;
                        $day = $setting["email_keys_expiry_noti_day$value"];
                        $keyData['text'] = "You have $ek Keys that are expiring in $day days";
                        $keyData['balance'] = "Keys Balance: $bk Keys";
                        $keyData['unique_id']  = $key->user->unique_id;
                        $keyData['provider']  = substr($key->user->email, strpos($key->user->email, '@') + 1);

                        try {
                            \Log::info(" $value Email key for APH" . $user->email);

                            Mail::to($key->user->email)->send(
                                new KeyExpiry($keyData)
                            );
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                    // if ($key->user->push_system_noti) {
                    //     $tokes = $key->user->deviceTokens->pluck('token')->toArray();
                    //     if (count($tokes)  > 0) {
                    //         sendNotification($pushNoti['key']['sub_title'], $pushNoti['key']['title'], $tokes);
                    //     }
                    // }
                    // InAppNoti::create([
                    //     'title' => $pushNoti['key']['title'],
                    //     'content' => $pushNoti['key']['sub_title'],
                    //     'type' => "System",
                    //     'user_id' => $key->user->id
                    // ]);
                }
            }

            // ============================SMS=================================
            $rewaerdDays = isset($setting["reward_expiry_noti_day$value"]) ? $setting["reward_expiry_noti_day$value"] : 0;
            $keyDays = isset($setting["keys_expiry_noti_day$value"]) ? $setting["keys_expiry_noti_day$value"] : 0;
            $aphdDays = isset($setting["aph_expiry_noti_day$value"]) ? $setting["aph_expiry_noti_day$value"] : 0;

            $rewardSMS = "Your rewards are expiring in $rewaerdDays days! Login to redeem your active rewards redemption before it expires.";

            $aphSMS = "Your Airport Pass is expiring soon! Remember to update your new Airport Pass ID & expiry date to retain your account and Keys earned!";

            $keySMS = "Your Keys are expiring in $keyDays days! Check your available Keys for rewards redemption before it expires.";




            if (isset($setting["aph_expiry_noti_day$value"])) {
                // SMS
                $aphEmail = AppUser::whereIn('status', ['Active', 'Inactive'])->where('user_type', 'Airport Pass Holder')->whereDate('expiry_date', Carbon::today()->addDays($setting["aph_expiry_noti_day$value"]))->get();
                foreach ($aphEmail as $user) {
                    if ($user->country_code === '+65' && $user->sms_noti) {
                        try {
                            \Log::info("$value SMS key for APH" . $user->phone_number);

                            //smsSend($aphSMS, $user->phone_number);
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                    // if ($user->push_system_noti) {

                    //     $tokes = $user->deviceTokens->pluck('token')->toArray();
                    //     if (count($tokes)  > 0) {
                    //         sendNotification($pushNoti['aph']['sub_title'], $pushNoti['aph']['title'],  $tokes);
                    //     }
                    // }
                    // InAppNoti::create([
                    //     'title' => $pushNoti['aph']['title'],
                    //     'content' => $pushNoti['aph']['sub_title'],
                    //     'type' => "System",
                    //     'user_id' => $user->id
                    // ]);
                }
            }
            if (isset($setting["reward_expiry_noti_day$value"])) {
                $userPurchasesReward = UserPurchasedReward::with(['user:id,name,email,email_noti,status' => function ($query) {
                    $query->whereIn('status', ['Active', 'Inactive']);
                }])->whereHas('user')->where('status', 'Purchased')->whereDate('expiry_date', Carbon::today()->addDays($setting["reward_expiry_noti_day$value"]))->get();
                foreach ($userPurchasesReward as $reward) {

                    if ($reward->user->country_code === '+65' && $reward->user->sms_noti) {
                        try {
                            \Log::info("$value SMS key for Reward" . $user->phone_number);
                            smsSend($rewardSMS, $reward->user->phone_number);
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                    // if ($reward->user->push_system_noti) {
                    //     $tokes = $reward->user->deviceTokens->pluck('token')->toArray();
                    //     if (count($tokes)  > 0) {
                    //         sendNotification($pushNoti['reward']['sub_title'], $pushNoti['reward']['title'], $tokes);
                    //     }
                    // }
                    // InAppNoti::create([
                    //     'title' => $pushNoti['reward']['title'],
                    //     'content' => $pushNoti['reward']['sub_title'],
                    //     'type' => "System",
                    //     'user_id' => $reward->user->id
                    // ]);
                }
            }
            if (isset($setting["keys_expiry_noti_day$value"])) {

                $keys  = KeyPassbookCredit::with(['user' => function ($query) {
                    $query->whereIn('status', ['Active', 'Inactive']);
                }])->whereHas('user')->select('*', DB::raw('count(*) as count'))->where([['remain_keys', '>', 0]])->whereDate('expiry_date', Carbon::today()->addDays($setting["keys_expiry_noti_day$value"]))->groupBy('user_id')->get();
                foreach ($keys as $key) {
                    if ($key->user->country_code === '+65' && $reward->user->sms_noti) {
                        try {
                            \Log::info("$value SMS key for key" . $user->phone_number);

                            smsSend($keySMS, $key->user->phone_number);
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                    // if ($key->user->push_system_noti) {
                    //     $tokes = $key->user->deviceTokens->pluck('token')->toArray();
                    //     if (count($tokes)  > 0) {
                    //         sendNotification($pushNoti['key']['sub_title'], $pushNoti['key']['title'], $tokes);
                    //     }
                    // } 
                    // InAppNoti::create([
                    //     'title' => $pushNoti['key']['title'],
                    //     'content' => $pushNoti['key']['sub_title'],
                    //     'type' => "System",
                    //     'user_id' => $key->user->id
                    // ]);
                }
            }
        }
    }
}
