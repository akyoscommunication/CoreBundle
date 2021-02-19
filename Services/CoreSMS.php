<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Services\SMSApi\MailjetSMS;
use Akyos\CoreBundle\Services\SMSApi\TwilioSMS;

class CoreSMS
{
	
	private $coreOptionsRepository;
	private $mailjetSMS;
	private $twilioSMS;
	
	public function __construct(CoreOptionsRepository $coreOptionsRepository, MailjetSMS $mailjetSMS, TwilioSMS $twilioSMS)
	{
		$this->coreOptionsRepository = $coreOptionsRepository;
		$this->mailjetSMS = $mailjetSMS;
		$this->twilioSMS = $twilioSMS;
	}
	
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
			return $this->mailjetSMS->sendSMS($phoneNumber, $body, $doNotFlush);
		}
		
		return false;
	}
	
	public function getCode(): int
	{
		return random_int(10000000, 99999999);
	}
}