<?php

namespace Akyos\CoreBundle\Services\SMSApi;

use Akyos\CoreBundle\Services\MessageLogger;
use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

// Needs composer require mailjet/mailjet-apiv3-php
class MailjetSMS
{
	private $sender;
	private $smsToken;
	private $messageLogger;
	private $flashBag;
	
	public function __construct(ParameterBagInterface $params, MessageLogger $messageLogger, FlashBagInterface $flashBag)
	{
		$this->smsToken = $params->get('mailjet_smsToken');
		$this->sender = $params->get('mailjet_sender');
		$this->messageLogger = $messageLogger;
		$this->flashBag = $flashBag;
	}
	
	public function sendSMS(string $phoneNumber, string $body, bool $doNotFlush = null)
	{
		$phoneNumber = static::transformNum($phoneNumber);
		// IF ERROR
		if (is_array($phoneNumber)) {
			$this->flashBag->add('danger', 'Le format du numéro de téléphone est invalide, il doit correspondre au format international E.164 (exemple +33612345678 pour la France)');
			return $phoneNumber;
		}
		
		$mailjet = new Client($this->smsToken, NULL, true, ['url' => "api.mailjet.com", 'version' => 'v4', 'call' => false]);
		
		$sms = [
			'Text' => $body,
			'To' => $phoneNumber,
			'From' => $this->sender
		];
		
		try {
			$response = $mailjet->post(Resources::$SmsSend, ['body' => $sms]);
			if (!$response->success()) {
				throw new \Exception(json_encode($response->getBody()));
			}
			$this->messageLogger->saveLog($sms, null, 'mailjet_sms', $doNotFlush);
			return true;
		} catch (\Exception $e) {
			$this->messageLogger->saveLog($sms, $e, 'mailjet_sms', $doNotFlush);
			return $e;
		}
	}
	
	public function transformNum($number)
	{
		preg_match_all('/^\+[1-9]\d{1,14}$/', $number, $matches);
		if (count($matches[0]) === 0) {
			return [
				"status" => false,
				"message" => "Format du numéro de téléphone invalide",
				"errorcode" => "ERRNUMFORMAT"
			];
		}
		$number = str_replace([".", " "], "", $number);
		
		return $number;
	}
}