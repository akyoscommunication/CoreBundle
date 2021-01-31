<?php

namespace Akyos\CoreBundle\Services\SMSApi;

use Akyos\CoreBundle\Services\MessageLogger;
use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

// Needs composer require mailjet/mailjet-apiv3-php
class MailjetSMS {
	
	private $sender;
	private $smsToken;
	private $messageLogger;
	
	public function __construct(ParameterBagInterface $params, MessageLogger $messageLogger)
	{
		$this->smsToken = $params->get('mailjet_smsToken');
		$this->sender = $params->get('mailjet_sender');
		$this->messageLogger = $messageLogger;
	}
	
	public function sendSMS(String $phoneNumber, String $body, bool $doNotFlush = null)
	{
		$phoneNumber = static::transformNum($phoneNumber);
		if (is_array($phoneNumber)) {
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
			if(!$response->success()) {
				throw new \Exception(json_encode($response->getBody()));
			}
			$this->messageLogger->saveLog($sms, null, 'mailjet_sms', $doNotFlush);
			return true;
		} catch (\Exception $e) {
			$this->messageLogger->saveLog($sms, $e, 'mailjet_sms', $doNotFlush);
			return $e;
		}
	}
	
	public static function transformNum($number)
	{
		preg_match_all("/^0([0-9].?){9}/", $number, $matches);
		if (count($matches[0])) {
			$number = preg_replace('/0/', '+33', $number, 1);
			$number = str_replace(".", "", $number);
			$number = str_replace(" ", "", $number);
		} else {
			preg_match_all('/^\+33([0-9].?){9}/', $number, $matches);
			if (count($matches[0]) == 0) {
				return [
					"status" => false,
					"message" => "Format du numéro de téléphone invalide",
					"errorcode" => "ERRNUMFORMAT"
				];
			}
			$number = str_replace(".", "", $number);
			$number = str_replace(" ", "", $number);
		}
		return $number;
	}
}