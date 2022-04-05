<?php

namespace Akyos\CoreBundle\Service;

use Akyos\CoreBundle\Service\MailApi\MailjetEmail;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CoreMailer
{
	private MailerInterface $mailer;
	private Environment $twig;
	private MessageLogger $messageLogger;
	private MailjetEmail $mailjetEmail;
	private ParameterBagInterface $parameterBag;
	
	public function __construct(MailerInterface $mailer, Environment $twig, ParameterBagInterface $parameterBag, MailjetEmail $mailjetEmail, MessageLogger $messageLogger)
	{
		$this->mailer = $mailer;
		$this->twig = $twig;
		$this->messageLogger = $messageLogger;
		$this->mailjetEmail = $mailjetEmail;
		$this->parameterBag = $parameterBag;
	}

    /**
     * @param $to
     * @param $subject
     * @param $body
     * @param $title
     * @param null $template
     * @param null $from
     * @param null $bcc
     * @param null $replyTo
     * @param null $attachment
     * @param array|null $options
     * @param bool|null $doNotFlush
     * @param null $backupSendMail
     * @return bool|Exception|TransportExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
	public function sendMail($to, $subject, $body, $title, $template = null, $from = null, $bcc = null, $replyTo = null, $attachment = null, array $options = null, bool $doNotFlush = null, $backupSendMail = null)
	{
		$email = new Email();
		$from = (is_null($from)) ? ($this->parameterBag->get('site_name') . ' <' . ($_SERVER['SERVER_NAME'] === "localhost" ? "thomas.sebert.akyos@gmail.com" : 'noreply@' . $_SERVER['SERVER_NAME']) . '>') : $from;
		$replyTo = (is_null($replyTo)) ? ($this->parameterBag->get('site_name') . ' <' . ($_SERVER['SERVER_NAME'] === "localhost" ? "thomas.sebert.akyos@gmail.com" : 'noreply@' . $_SERVER['SERVER_NAME']) . '>') : $replyTo;
		
		$bodyParams = [
			'subject' => $subject,
			'title' => $title,
			'body' => $body,
		];
		if (isset($options['templateParams'])) {
			$bodyParams = array_merge($bodyParams, $options['templateParams']);
		}
		$body = $this->twig->render($template ?: '@AkyosCore/email/default.html.twig', $bodyParams);

		if (!$backupSendMail && $this->parameterBag->get('email_transport') === "Mailjet API" && $this->parameterBag->get('kernel.environment') === "prod") {
            return $this->mailjetEmail->sendEmail($to, $subject, $body, $from, $bcc, $attachment, $options, $doNotFlush);
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
			foreach ($options['attachments'] as $attached) {
				if (is_array($attached)) {
					$email->attachFromPath($attached['path'], $attached['name']);
				} else {
					$email->attachFromPath($attached);
				}
			}
		}
		
		try {
			$this->mailer->send($email);
			$this->messageLogger->saveLog($email, null, 'core_email');
			return true;
		} catch (TransportExceptionInterface $e) {
//			dd($e);
			$this->messageLogger->saveLog($email, $e, 'core_email');
			return $e;
		}
	}
}