<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\AppUser;
use App\Mail\BroadcastEmail;
use App\Models\BroadcastMsg;
use App\Models\DeviceToken;
use App\Models\InAppNotiAll;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class BroadcastNoti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:notification';

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
        $now = Carbon::now();
        \Log::info("$now -- NOW");
        $emails = AppUser::whereIn('status',['Active','Inactive'])->where('email_noti', 1)->pluck('email', 'unique_id');
        $phones = AppUser::whereIn('status',['Active','Inactive'])->where([['sms_noti', 1], ['country_code', '+65']])->pluck('phone_number');
        $broadcast =   BroadcastMsg::whereDate('date_of_publish', $now->format('Y-m-d'))->whereTime('date_of_publish', $now->format('H:i') . ':00')->get();
        // $broadcast =   BroadcastMsg::get();
        // $broadcast =   BroadcastMsg::get();

        foreach ($broadcast as $key => $value) {
            $ed['email_content'] = $value->email_content;
            $attachments = [];

            if($value->attachments){
                $attachments = explode(',', $value->attachments);
                
            }
            $i  = 0;
            if (!$value->csv_file) {

                if ($value->email_subject && $value->email_content) {
                    try {
                        // $firstEmail = $emails[0];
                        // array_shift($emails);
                        $chunks = $emails->chunk(99)->toArray();
                        foreach ($chunks as $key => $emilChunk) {
                            foreach ($emilChunk as $uid => $email) {

                                $ed['unique_id'] = $uid;
                                $ed['en_email'] = Crypt::encryptString($email);
                                Mail::to($email)->send(
                                    new BroadcastEmail($value->email_subject, $ed,$attachments)
                                );
                            }
                        }

                        // Mail::bcc($emails)->send(
                        //     new BroadcastEmail($value->email_subject, $value->email_content)
                        // );
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                }
                if ($value->sms_content) {
                    foreach ($phones as  $phone) {
                        smsSend($value->sms_content, $phone);
                    }
                }
                if ($value->inapp_title && $value->inapp_content) {

                    AppUser::where('id', '>', 0)->increment('noti_count', 1);
                    InAppNotiAll::create([
                        'title' => $value->inapp_title,
                        'content' => $value->inapp_content,
                        'type' => $value->type
                    ]);
                }
                if ($value->push_title && $value->push_subtitle) {
                    if ($value->type === 'Other') {
                        $users  = AppUser::where([['push_system_noti', 1]])->pluck('id');
                    } else {
                        $users  = AppUser::where([['push_system_noti', 1]])->pluck('id');
                    }
                    $tokens =  DeviceToken::whereIn('user_id', $users)->pluck('token')->toArray();
                    if (count($tokens)  > 0) {
                        sendNotification($value->push_subtitle, $value->push_title, $tokens);
                    }
                }
            } else {
                if ($value->email_subject && $value->email_content) {
                    try {

                        $filePath = public_path('report') . '/' . $value->csv_file;

                        if (($handle = fopen($filePath, 'r')) !== false) {

                            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                                if ($i == 0) {
                                    $header = $row;
                                } else {

                                    $user =   AppUser::where([['email', $row[0]], ['email_noti', '0']])->first();


                                    if (!$user) {

                                        $ed['unique_id'] = $row[1];
                                        $email = $row[0];
                                        $ed['en_email'] = Crypt::encryptString($email);
                                        Mail::to($row[0])->send(
                                            new BroadcastEmail($value->email_subject, $ed,$attachments)
                                        );
                                    }
                                }
                                $i++;
                            }
                        }

                        // Mail::bcc($emails)->send(
                        //     new BroadcastEmail($value->email_subject, $value->email_content)
                        // );
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                }
            }

            $value->update(['status' => 'Send']);
        }

        //
    }
}
