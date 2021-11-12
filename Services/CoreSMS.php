<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Services\SMSApi\MailjetSMS;
use Akyos\CoreBundle\Services\SMSApi\TwilioSMS;
use Exception;

class CoreSMS
{
	private CoreOptionsRepository $coreOptionsRepository;
	private MailjetSMS $mailjetSMS;
	private TwilioSMS $twilioSMS;
	
	public function __construct(CoreOptionsRepository $coreOptionsRepository, MailjetSMS $mailjetSMS, TwilioSMS $twilioSMS)
	{
		$this->coreOptionsRepository = $coreOptionsRepository;
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
		$coreOptions = $this->coreOptionsRepository->findAll();
		if ($coreOptions) {
			$coreOptions = $coreOptions[0];
		}
		
		if ($coreOptions->getSMSTransport() === "Mailjet SMS") {
			return $this->mailjetSMS->sendSMS($phoneNumber, $body, $doNotFlush);
		}
		
		if ($coreOptions->getSMSTransport() === "Twilio SMS") {
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