<?php

namespace Akyos\CoreBundle\Service;

use Akyos\CoreBundle\Service\SMSApi\MailjetSMS;
use Akyos\CoreBundle\Service\SMSApi\TwilioSMS;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CoreSMS
{
    private ParameterBagInterface $parameterBag;

    private MailjetSMS $mailjetSMS;

    private TwilioSMS $twilioSMS;

    public function __construct(ParameterBagInterface $parameterBag, MailjetSMS $mailjetSMS, TwilioSMS $twilioSMS)
    {
        $this->parameterBag = $parameterBag;
        $this->mailjetSMS = $mailjetSMS;
        $this->twilioSMS = $twilioSMS;
    }

    /**
     * @param string $phoneNumber
     * @param string $body
     * @param bool|null $doNotFlush
     * @return array|bool|Exception|string|string[]
     */
    public function sendSMS(string $phoneNumber, string $body, bool $doNotFlush = null)
    {
        if ($this->parameterBag->get('sms_transport') === "Mailjet SMS") {
            return $this->mailjetSMS->sendSMS($phoneNumber, $body, $doNotFlush);
        }

        if ($this->parameterBag->get('sms_transport') === "Twilio SMS") {
            return $this->twilioSMS->sendSMS($phoneNumber, $body, $doNotFlush);
        }

        return false;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        try {
            return random_int(10000000, 99999999);
        } catch (Exception $e) {
//            dd($e);
            return 10000000;
        }
    }
}