<?php


namespace App\Services;


use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;


class MobileService
{
    static function sendActivationCode(string $mobile, string $code)
    {
        return self::sendSmsByBaseNumber($mobile, 14615, [$code]);
    }

    static function sendTicketReplied(string $mobile, string $ticket_id)
    {
        return self::sendSmsByBaseNumber($mobile, 14653, [$ticket_id]);
    }

    static function sendInvoiceCreated(string $mobile, string $invoice_id, string $price)
    {
        return self::sendSmsByBaseNumber($mobile, 14654, [$invoice_id, $price]);
    }

    static function sendSmsByBaseNumber(string $mobile, string $body_id, array $params)
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        try {
            $client = new SoapClient('http://api.payamak-panel.com/post/Send.asmx?wsdl', array('encoding' => 'UTF-8'));
            $parameters['username'] = config('sms.username');
            $parameters['password'] = config('sms.password');
            $parameters['text'] = $params;
            $parameters['to'] = $mobile;
            $parameters['bodyId'] = $body_id;
            $result = $client->SendByBaseNumber؛($parameters);
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
