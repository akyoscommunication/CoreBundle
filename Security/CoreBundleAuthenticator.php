<?php

namespace Akyos\CoreBundle\Security;

use Akyos\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
// TODO => Refaire les authenticator, ça a tout changé...
class CoreBundleAuthenticator extends AbstractFormLoginAuthenticator
{
	use TargetPathTrait;

	private EntityManagerInterface $entityManager;
	private UrlGeneratorInterface $urlGenerator;
	private CsrfTokenManagerInterface $csrfTokenManager;
	private UserPasswordHasherInterface $passwordHasher;

	public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordHasherInterface $passwordHasher)
	{
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
		$this->csrfTokenManager = $csrfTokenManager;
		$this->passwordHasher = $passwordHasher;
	}

	public function supports(Request $request): bool
    {
		return 'app_login' === $request->attributes->get('_route')
			&& $request->isMethod('POST');
	}

	public function getCredentials(Request $request): array
    {
		$credentials = [
			'email' => $request->request->get('email'),
			'password' => $request->request->get('password'),
			'csrf_token' => $request->request->get('_csrf_token'),
		];
		$request->getSession()->set(
			Security::LAST_USERNAME,
			$credentials['email']
		);

		return $credentials;
	}

	public function getUser($credentials, UserProviderInterface $userProvider)
	{
		$token = new CsrfToken('authenticate', $credentials['csrf_token']);
		if (!$this->csrfTokenManager->isTokenValid($token)) {
			throw new InvalidCsrfTokenException('Jeton CSRF invalide.');
		}

		$user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

		if (!$user) {
			// fail authentication with a custom error
			throw new CustomUserMessageAuthenticationException('Cet email n\'est associé à aucun compte utilisateur.');
		}

		return $user;
	}

	public function checkCredentials($credentials, UserInterface $user): bool
    {
		return $this->passwordHasher->isPasswordValid($user, $credentials['password']);
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
		if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
			return new RedirectResponse($targetPath);
		}

		return new RedirectResponse($this->urlGenerator->generate('core_index'));
	}

	protected function getLoginUrl(): string
    {
		return $this->urlGenerator->generate('app_login');
	}
}
