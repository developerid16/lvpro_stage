<?php

use GuzzleHttp\Client;
use App\Models\SaleBatch;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
// use OneSignal;
use Carbon\Carbon;

if (!function_exists('statusHtmlToggle')) {
    function statusHtmlToggle($status)
    {
        $s = $status == 'active' ? 'checked' : '';
        return "<div class='form-check form-switch form-switch-md mb-3' dir='ltr'>
                    <input class='form-check-input' value='active'  type='checkbox' $s>
                </div>";
    }
}
if (!function_exists('imageDBTag')) {
    function imageDBTag($image)
    {
        return "<img src='$image' class='avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle' alt='img'>";
    }
}
if (!function_exists('numberFormat')) {
    function numberFormat($value, $currency = false): string
    {
        $amt = number_format($value, 2, '.', ',');
        if ($currency)
            $amt = "$" . $amt;
        return $amt;
    }
}
if (!function_exists('fileUploaded')) {
    function fileUploaded($batchId): void
    {
        SaleBatch::find($batchId)->update([
            'status' => "Completed"
        ]);
    }
}

if (!function_exists('smsSend')) {
    function smsSend($sms, $number): void
    {

        $apiusername = config('safra.apiusername');
        $apipassword = config('safra.apipassword');
        $mobileno = '65' . $number;
        $senderid = urlencode(config('safra.senderid'));
        $languagetype = config('safra.languagetype');
        $message = urlencode("$sms");

        $curl = curl_init();
Log::info("http://gateway.onewaysms.sg:10002/api.aspx?apiusername=$apiusername&apipassword=$apipassword&mobileno=$mobileno&senderid=$senderid&languagetype=$languagetype&message=$message");
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://gateway.onewaysms.sg:10002/api.aspx?apiusername=$apiusername&apipassword=$apipassword&mobileno=$mobileno&senderid=$senderid&languagetype=$languagetype&message=$message",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        Log::info("$response");
    }
}
if (!function_exists('sendNotification')) {
    function sendNotification($headings, $subtitle, $deviceid): void
    {


        $userId = ["f121ecb0-371d-48ed-bfe7-0c1180b3ceb0",];
        //    $dd =  \OneSignal::sendNotificationToUser(
        //         $headings,
        //         "cc10f1b6-d23c-7c93-851b-b29d0ae46991",
        //         null,
        //         null,
        //         null,
        //         null,

        //     );
        \OneSignal::sendNotificationToUser(
            $headings,
            $deviceid,
            $url = null,
            $data = null,
            $buttons = null,
            $schedule = null,
            $subtitle,
            $subtitle = null
        );
        // dd($dd);
    }
}

if (!function_exists('generateNumericOTP')) {

    function generateNumericOTP($n)
    {

        // Take a generator string which consist of
        // all numeric digits
        $generator = "1357902468";

     

        $result = "";

        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }

        // Return result
        return $result;
    }
}
if (!function_exists('keyExpiryDate')) {

    function keyExpiryDate()
    {
        $today = Carbon::now();
        $month = $today->month;
        $year = $today->year;
        if ($month >= 9 && $month <= 12) {
            // need to expire next year
            $year++;
        }
        return  Carbon::createFromFormat('Y-m-d', "$year-11-30");
    }
}

if (!function_exists('tireExpiryDate')) {

    function tireExpiryDate()
    {
        $today = Carbon::now();
        $month = $today->month;
        $year = $today->year;
        if ($month >= 9 && $month <= 12) {
            // need to expire next year
            $year++;
        }
        return  Carbon::createFromFormat('Y-m-d', "$year-08-31");
    }
}

if (!function_exists('reward_expired')) {

    function reward_expired($reward)
    {
        if (!$reward || !$reward->sales_end_date) {
            return false;
        }

        $endDateTime = \Carbon\Carbon::parse(
            $reward->sales_end_date . ' ' . ($reward->sales_end_time ?? '23:59:59')
        );

        return $endDateTime->isPast();
    }
}
