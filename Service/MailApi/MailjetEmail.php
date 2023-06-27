<?php

namespace Akyos\CoreBundle\Service\MailApi;

use Akyos\CoreBundle\Service\ErrorCatcher;
use Akyos\CoreBundle\Service\MessageLogger;
use Exception;
use Mailjet\Client;
use Mailjet\Resources;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

// Needs composer require mailjet/mailjet-apiv3-php
class MailjetEmail
{
    private $apiKey;

    private $secretKey;

    private MessageLogger $messageLogger;

    private ErrorCatcher $catcher;

    public function __construct(ParameterBagInterface $params, MessageLogger $messageLogger, ErrorCatcher $catcher)
    {
        $this->apiKey = $params->get('mailjet_apiKey');
        $this->secretKey = $params->get('mailjet_secretKey');
        $this->messageLogger = $messageLogger;
        $this->catcher = $catcher;
    }

    /**
     * @param $to
     * @param $subject
     * @param $body
     * @param $from
     * @param null $bcc
     * @param null $attachment
     * @param array|null $options
     * @param bool|null $doNotFlush
     * @return bool
     */
    public function sendEmail($to, $subject, $body, $from, $bcc = null, $attachment = null, array $options = null, bool $doNotFlush = null): bool
    {
        if (is_array($from) && count($from)) {
            if (array_values($from) !== $from) {
                $from = ['Email' => array_keys($from)[0], 'Name' => $from[array_keys($from)[0]] ?: array_keys($from)[0]];
            } else {
                $from = ['Email' => $from[0], 'Name' => $from[0]];
            }
        } else {
            $from = explode('<', (string)$from)[count(explode('<', (string)$from)) - 1];
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
            $attachmentsArray[] = ['ContentType' => mime_content_type($attachment), 'Filename' => explode('/', $attachment)[count(explode('/', $attachment)) - 1], 'Base64Content' => base64_encode($attachment)];
        }
        if (isset($options['attachments']) && !empty($options['attachments']) && !is_null($options['attachments'])) {
            foreach ($options['attachments'] as $attached) {
                if (is_array($attached)) {
                    $attachmentsArray[] = ['ContentType' => mime_content_type($attached['path']), 'Filename' => $attached['name'], 'Base64Content' => base64_encode($attached['path'])];
                } else {
                    $attachmentsArray[] = ['ContentType' => mime_content_type($attached), 'Filename' => explode('/', $attached)[count(explode('/', $attached)) - 1], 'Base64Content' => base64_encode($attached)];
                }
            }
        }

        $mailjet = new Client($this->apiKey, $this->secretKey, true, ['version' => 'v3.1']);
        $email = ['SandboxMode' => false, 'Messages' => [['From' => $from, 'To' => $toArray, 'Bcc' => $bccArray, 'Subject' => $subject, 'HTMLPart' => $body, 'Attachments' => $attachmentsArray,]]];

        try {
            $response = $mailjet->post(Resources::$Email, ['body' => $email]);
            if (!$response->success()) {
                // throw new RuntimeException(json_encode($response->getData(), JSON_THROW_ON_ERROR));
            }
            $this->messageLogger->saveLog($email, null, 'mailjet_email', $doNotFlush);
            return true;
        } catch (Exception $e) {
            $this->messageLogger->saveLog($email, $e, 'mailjet_email', $doNotFlush);
            return $this->catcher->catch($e);
        }
    }
}
