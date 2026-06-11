<?php

namespace Akyos\CoreBundle\Service\SMSApi;

use Akyos\CoreBundle\Service\ErrorCatcher;
use Akyos\CoreBundle\Service\MessageLogger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twilio\Rest\Client;

// Needs composer require twilio/sdk
class TwilioSMS
{
    private $sender;

    private $accountSID;

    private $authToken;

    public function __construct(ParameterBagInterface $params, private readonly MessageLogger $messageLogger, private readonly ErrorCatcher $catcher)
    {
        $this->accountSID = $params->get('twilio_accountSID');
        $this->authToken = $params->get('twilio_authToken');
        $this->sender = $params->get('twilio_sender');
    }

    public function sendSMS(string $phoneNumber, string $body, ?bool $doNotFlush = null): true
    {
        $phone = self::transformNum($phoneNumber);
        if (is_array($phone)) {
            return $phone;
        }

        $client = new Client($this->accountSID, $this->authToken);

        $sms = [$phone, ['from' => $this->sender, 'body' => $body]];

        try {
            $client->messages->create(...$sms);
            $this->messageLogger->saveLog($sms, null, 'twilio_sms', $doNotFlush);
            return true;
        } catch (\Exception $e) {
            $this->messageLogger->saveLog($sms, $e, 'twilio_sms', $doNotFlush);
            return $this->catcher->catch($e);
        }
    }

    /**
     * @param $number
     * @return array|string|string[]
     */
    public static function transformNum($number): array|string
    {
        preg_match_all("/^0(\\d.?){9}/", (string) $number, $matches);
        if (count($matches[0])) {
            $number = preg_replace('/0/', '+33', (string) $number, 1);
            $number = str_replace([".", " "], "", $number);
        } else {
            preg_match_all('/^\+33(\d.?){9}/', (string) $number, $matches);
            if (count($matches[0]) === 0) {
                return ["status" => false, "message" => "Format du numéro de téléphone invalide", "errorcode" => "ERRNUMFORMAT"];
            }
            $number = str_replace([".", " "], "", $number);
        }
        return $number;
    }
}