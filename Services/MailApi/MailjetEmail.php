<?php

namespace Akyos\CoreBundle\Services\MailApi;

use Akyos\CoreBundle\Services\MessageLogger;
use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

// Needs composer require mailjet/mailjet-apiv3-php
class MailjetEmail
{
	
	private $apiKey;
	private $secretKey;
	private $messageLogger;
	
	public function __construct(ParameterBagInterface $params, MessageLogger $messageLogger)
	{
		$this->apiKey = $params->get('mailjet_apiKey');
		$this->secretKey = $params->get('mailjet_secretKey');
		$this->messageLogger = $messageLogger;
	}
	
	public function sendEmail($to, $subject, $body, $from, $bcc = null, $attachment = null, array $options = null, bool $doNotFlush = null)
	{
		if (is_array($from) && count($from)) {
			if (array_values($from) !== $from) {
				$from = ['Email' => array_keys($from)[0], 'Name' => $from[array_keys($from)[0]] ? $from[array_keys($from)[0]] : array_keys($from)[0]];
			} else {
				$from = ['Email' => $from[0], 'Name' => $from[0]];
			}
		} else {
			$from = explode('<', $from)[count(explode('<', $from)) - 1];
			$from = explode('>', $from)[0];
			$from = ['Email' => $from, 'Name' => $from];
		}
		
		$toArray = [];
		if (is_array($to) && count($to)) {
			if (array_values($to) !== $to) {
				foreach ($to as $key => $value) {
					$toArray[] = ['Email' => $key, 'Name' => $value];
				}
			} else {
				foreach ($to as $value) {
					$toArray[] = ['Email' => $value, 'Name' => $value];
				}
			}
		} else {
			$toArray[] = ['Email' => $to, 'Name' => $to];
		}
		
		$bccArray = [];
		if ($bcc) {
			if (is_array($bcc) && count($bcc)) {
				if (array_values($bcc) !== $bcc) {
					foreach ($bcc as $key => $value) {
						$bccArray[] = ['Email' => $key, 'Name' => $value];
					}
				} else {
					foreach ($bcc as $value) {
						$bccArray[] = ['Email' => $value, 'Name' => $value];
					}
				}
			} else {
				$bccArray[] = ['Email' => $bcc, 'Name' => $bcc];
			}
		}
		
		$attachmentsArray = [];
		if (!empty($attachment)) {
			$attachmentsArray[] = [
				'ContentType' => mime_content_type($attachment),
				'Filename' => explode('/', $attachment)[count(explode('/', $attachment)) - 1],
				'Base64Content' => base64_encode($attachment)
			];
		}
		if (isset($options['attachments']) && !empty($options['attachments']) && !is_null($options['attachments'])) {
			foreach ($options['attachments'] as $attachment) {
				if (is_array($attachment)) {
					$attachmentsArray[] = [
						'ContentType' => mime_content_type($attachment['path']),
						'Filename' => $attachment['name'],
						'Base64Content' => base64_encode($attachment['path'])
					];
				} else {
					$attachmentsArray[] = [
						'ContentType' => mime_content_type($attachment),
						'Filename' => explode('/', $attachment)[count(explode('/', $attachment)) - 1],
						'Base64Content' => base64_encode($attachment)
					];
				}
			}
		}
		
		$mailjet = new Client($this->apiKey, $this->secretKey, true, ['version' => 'v3.1']);
		$email = [
			'SandboxMode' => "false",
			'Messages' => [
				[
					'From' => $from,
					'To' => $toArray,
					'Bcc' => $bccArray,
					'Subject' => $subject,
					'HTMLPart' => $body,
					'Attachments' => $attachmentsArray,
				]
			]
		];
		
		
		try {
			$response = $mailjet->post(Resources::$Email, ['body' => $email]);
			if (!$response->success()) {
				throw new \Exception(json_encode($response->getData()));
			}
			$this->messageLogger->saveLog($email, null, 'mailjet_email', $doNotFlush);
			return true;
		} catch (\Exception $e) {
			$this->messageLogger->saveLog($email, $e, 'mailjet_email', $doNotFlush);
			return $e;
		}
	}
}