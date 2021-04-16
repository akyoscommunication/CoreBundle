<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Services\MailApi\MailjetEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class CoreMailer
{
	
	private $mailer;
	private $twig;
	private $coreOptionsRepository;
	private $messageLogger;
	private $mailjetEmail;
	private $parameterBag;
	
	public function __construct(MailerInterface $mailer, Environment $twig, ParameterBagInterface $parameterBag, CoreOptionsRepository $coreOptionsRepository, MailjetEmail $mailjetEmail, MessageLogger $messageLogger)
	{
		$this->mailer = $mailer;
		$this->twig = $twig;
		$this->coreOptionsRepository = $coreOptionsRepository;
		$this->messageLogger = $messageLogger;
		$this->mailjetEmail = $mailjetEmail;
		$this->parameterBag = $parameterBag;
	}
	
	
	public function sendMail($to, $subject, $body, $title, $template = null, $from = null, $bcc = null, $replyTo = null, $attachment = null, array $options = null, bool $doNotFlush = null, $backupSendMail = null)
	{
		$coreOptions = $this->coreOptionsRepository->findAll();
		if ($coreOptions) {
			$coreOptions = $coreOptions[0];
		}
		
		$email = new Email();
		$from = (is_null($from)) ? ($coreOptions && $coreOptions->getSiteTitle() ? $coreOptions->getSiteTitle() : 'noreply') . ' <' . ($_SERVER['SERVER_NAME'] === "localhost" ? "thomas.sebert.akyos@gmail.com" : 'noreply@' . $_SERVER['SERVER_NAME']) . '>' : $from;
		$replyTo = (is_null($replyTo)) ? ($coreOptions && $coreOptions->getSiteTitle() ? $coreOptions->getSiteTitle() : 'noreply') . ' <' . ($_SERVER['SERVER_NAME'] === "localhost" ? "thomas.sebert.akyos@gmail.com" : 'noreply@' . $_SERVER['SERVER_NAME']) . '>' : $replyTo;
		
		$bodyParams = [
			'subject' => $subject,
			'title' => $title,
			'body' => $body,
		];
		if (isset($options['templateParams'])) {
			$bodyParams = array_merge($bodyParams, $options['templateParams']);
		}
		$body = $this->twig->render($template ?: '@AkyosCore/email/default.html.twig', $bodyParams);

		if (!$backupSendMail) {
            if ($coreOptions->getEmailTransport() === "Mailjet API" && $this->parameterBag->get('kernel.environment') === "prod") {
                return $this->mailjetEmail->sendEmail($to, $subject, $body, $from, $bcc, $attachment, $options, $doNotFlush);
            }
        }
		
		if (is_array($from) && count($from)) {
			if (array_values($from) !== $from) {
				$email->from(new Address($from[array_keys($from)[0]] ? $from[array_keys($from)[0]] . ' <' . array_keys($from)[0] . '>' : array_keys($from)[0]));
				array_shift($from);
				foreach ($from as $key => $value) {
					$email->addFrom(new Address($value ? $value . ' <' . $key . '>' : $key));
				}
			} else {
				$email->from(...$from);
			}
		} else {
			$email->from($from);
		}
		
		if (is_array($to) && count($to)) {
			if (array_values($to) !== $to) {
				$email->to(new Address($to[array_keys($to)[0]] ? $to[array_keys($to)[0]] . ' <' . array_keys($to)[0] . '>' : array_keys($to)[0]));
				array_shift($to);
				foreach ($to as $key => $value) {
					$email->addTo(new Address($value ? $value . ' <' . $key . '>' : $key));
				}
			} else {
				$email->to(...$to);
			}
		} else {
			$email->to($to);
		}
		
		if (is_array($bcc) && count($bcc)) {
			if (array_values($bcc) !== $bcc) {
				$email->bcc(new Address($bcc[array_keys($bcc)[0]] ? $bcc[array_keys($bcc)[0]] . ' <' . array_keys($bcc)[0] . '>' : array_keys($bcc)[0]));
				array_shift($bcc);
				foreach ($bcc as $key => $value) {
					$email->addBcc(new Address($value ? $value . ' <' . $key . '>' : $key));
				}
			} else {
				$email->bcc(...$bcc);
			}
		} elseif ($bcc) {
			$email->bcc($bcc);
		}
		
		$email
			->subject($subject)
			->html($body)
			->replyTo($replyTo);
		
		if (!empty($attachment)) {
			$email->attach($attachment);
		}
		if (isset($options['attachments']) && !empty($options['attachments']) && !is_null($options['attachments'])) {
			foreach ($options['attachments'] as $attachment) {
				if (is_array($attachment)) {
					$email->attachFromPath($attachment['path'], $attachment['name']);
				} else {
					$email->attachFromPath($attachment);
				}
			}
		}
		
		try {
			$this->mailer->send($email);
			$this->messageLogger->saveLog($email, null, 'core_email', null);
			return true;
		} catch (TransportExceptionInterface $e) {
//			dd($e);
			$this->messageLogger->saveLog($email, $e, 'core_email', null);
			return $e;
		}
	}
}