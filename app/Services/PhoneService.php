<?php


namespace App\Services;


use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

class PhoneService
{
    static function sendActivationCode(string $phone, string $code)
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        try {
            $text = "کد فعال سازی حساب کاربری شما در ابرپرداز " . $code ." می باشد";
            $client = new SoapClient('http://api.payamak-panel.com/post/Voice.asmx?wsdl', array('encoding' => 'UTF-8'));
            $parameters['username'] = config('sms.username');
            $parameters['password'] = config('sms.password');
            $parameters['smsBody'] = $code;
            $parameters['speechBody'] = $code;
            $parameters['from'] = '10002012';
            $parameters['to'] = $phone;
            $result = $client->SendSMSWithSpeechText؛؛($parameters);
            $result = json_decode($result);
            if ($result->value > 100)
                return true;
            else
                return false;
        } catch (SoapFault $ex) {
            Log::error($ex);
            return false;
        }

    }
}
