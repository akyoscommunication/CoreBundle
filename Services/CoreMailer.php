<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class CoreMailer {
	
	private $mailer;
	private $twig;
	private $from;
	private $reply;
	
	public function __construct(MailerInterface $mailer, Environment $twig, CoreOptionsRepository $coreOptionsRepository, RequestStack $requestStack)
	{
		$this->mailer = $mailer;
		$this->twig = $twig;
		
		$coreOptions = $coreOptionsRepository->findAll();
		if($coreOptions) {
			$coreOptions = $coreOptions[0];
		}
		$noreply = ($coreOptions ? $coreOptions->getSiteTitle() : 'noreply').' <noreply@' . $requestStack->getCurrentRequest()->getHost().'>';
		$this->from = $noreply;
		$this->reply = $noreply;
	}
	
	
	public function sendMail($to, $subject, $body, $title, $template = '@AkyosCore/email/default.html.twig', $from = null, $bcc = null, $replyTo = null, $attachment = null, array $options = null)
	{
		$email = new Email();
		$from = (is_null($from)) ? $this->from : $from;
		$replyTo = (is_null($replyTo)) ? $this->reply : $replyTo;
		
		$body = $this->twig->render($template, array(
			'subject' => $subject,
			'title' => $title,
			'body' => $body,
			...$options['templateParams']
		));
		
		if(is_array($from) && count($from)) {
			if(array_values($from) !== $from) {
				$email->from(new Address($from[array_keys($from)[0]] ? $from[array_keys($from)[0]].' <'.array_keys($from)[0].'>' : array_keys($from)[0]));
				array_shift($from);
				foreach ($from as $key => $value) {
					$email->addFrom(new Address($value ? $value.' <'.$key.'>' : $key));
				}
			} else {
				$email->from(...$from);
			}
		}
		
		if(is_array($to) && count($to)) {
			if(array_values($to) !== $to) {
				$email->to(new Address($to[array_keys($to)[0]] ? $to[array_keys($to)[0]].' <'.array_keys($to)[0].'>' : array_keys($to)[0]));
				array_shift($to);
				foreach ($to as $key => $value) {
					$email->addTo(new Address($value ? $value.' <'.$key.'>' : $key));
				}
			} else {
				$email->to(...$to);
			}
		}
		
		if(is_array($bcc) && count($bcc)) {
			if(array_values($bcc) !== $bcc) {
				$email->bcc(new Address($bcc[array_keys($bcc)[0]] ? $bcc[array_keys($bcc)[0]].' <'.array_keys($bcc)[0].'>' : array_keys($bcc)[0]));
				array_shift($bcc);
				foreach ($bcc as $key => $value) {
					$email->addBcc(new Address($value ? $value.' <'.$key.'>' : $key));
				}
			} else {
				$email->bcc(...$bcc);
			}
		}
		
		$email
			->subject($subject)
			->html($body)
			->replyTo($replyTo)
		;
		
		if (!empty($attachment)) {
			$email->attach($attachment);
		}
		if (isset($options['attachments']) && !empty($options['attachments']) && !is_null($options['attachments'])) {
			foreach ($options['attachments'] as $attachment) {
				if(is_array($attachment)) {
					$email->attachFromPath($attachment['path'], $attachment['name']);
				} else {
					$email->attachFromPath($attachment);
				}
			}
		}
		
		try {
			$this->mailer->send($email);
			return true;
		} catch (TransportExceptionInterface $e) {
			return $e;
		}
	}
}