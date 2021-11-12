<?php

namespace Akyos\CoreBundle\Controller\Back;

use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/app", name="app_")
 */
class SecurityController extends AbstractController
{
	/**
	 * @Route("/login", name="login")
	 * @param AuthenticationUtils $authenticationUtils
	 * @return Response
	 */
	public function login(AuthenticationUtils $authenticationUtils): Response
	{
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('@AkyosCore/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
	}

    /**
     * @Route("/logout", name="logout")
     * @throws Exception
     */
	public function logout(): void
    {
		throw new RuntimeException('This method can be blank - it will be intercepted by the logout key on your firewall');
	}
}
