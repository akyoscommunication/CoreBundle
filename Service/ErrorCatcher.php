<?php

namespace Akyos\CoreBundle\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ErrorCatcher
{
	private FlashBagInterface $flashBag;
    private ParameterBagInterface $parameterBag;
    private MailerInterface $mailer;

    public function __construct(FlashBagInterface $flashBag, ParameterBagInterface $parameterBag, MailerInterface $mailer)
	{
		$this->flashBag = $flashBag;
        $this->parameterBag = $parameterBag;
        $this->mailer = $mailer;
    }

    /**
     * @param Exception $e
     * @return false
     * @throws TransportExceptionInterface
     */
	public function catch(\Exception $e): bool
    {
        if($this->parameterBag->get('APP_ENV') === 'dev') {
            dd($e);
        } else {
            try {
                $this->mailer->send((new Email())
                    ->to("thomas.sebert.akyos@gmail.com")
                    ->subject('Nouvelle erreur sur le site ' . $this->parameterBag->get('site_name'))
                    ->text($e)
                    ->addBcc("lilian.akyos@gmail.com")
                    ->addBcc("johan@akyos.com")
                );
            } catch (\Exception $e) {
            }
        }
        $this->flashBag->add('danger', 'Une erreur est survenue, veuillez réessayer. Si le problème persiste, veuillez contacter l\'équipe technique.');
        return false;
	}
}