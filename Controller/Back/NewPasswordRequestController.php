<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\NewPasswordRequest;
use Akyos\CoreBundle\Form\ChangePasswordType;
use Akyos\CoreBundle\Form\NewPasswordRequestType;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\NewPasswordRequestRepository;
use Akyos\CoreBundle\Services\CoreMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

/**
 * @Route("/app/new_password_request", name="new_password")
 */
class NewPasswordRequestController extends AbstractController
{
    /**
     * @Route("/{type}/{route}", name="", methods={"GET", "POST"})
     * @param string $type
     * @param string $route
     * @param NewPasswordRequestRepository $newPasswordRequestRepository
     * @param Request $request
     * @param TokenGeneratorInterface $tokenGenerator
     * @param CoreMailer $mailer
     * @param CoreOptionsRepository $coreOptionsRepository
     * @return Response
     * @throws \Exception
     */
    public function newPasswordRequest(string $type, string $route, NewPasswordRequestRepository $newPasswordRequestRepository, Request $request, TokenGeneratorInterface $tokenGenerator, CoreMailer $mailer, CoreOptionsRepository $coreOptionsRepository): Response
    {
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            $coreOptions = $coreOptions[0];
        }

        $type = explode(';', urldecode($type));

        $newPasswordRequest = new NewPasswordRequest();
        $newPasswordRequest->setUserRoute($route);

        $form = $this->createForm(NewPasswordRequestType::class, $newPasswordRequest);
        $form->handleRequest($request);

        $message = "";

        if ($form->isSubmitted() && $form->isValid()) {
			$entityManager = $this->getDoctrine()->getManager();

			$user = null;
			foreach ($type as $testedType) {
			    $testedType = implode('\\', explode('_', $testedType));
				if (class_exists('Akyos\\CoreBundle\\Entity\\' . $testedType)) {
					$user = $this->getDoctrine()->getRepository('Akyos\\CoreBundle\\Entity\\' . $testedType)->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
					$newPasswordRequest->setUserType($testedType);
				}
				if (!$user) {
					if (class_exists('App\\Entity\\' . $testedType)) {
						$user = $this->getDoctrine()->getRepository('App\\Entity\\' . $testedType)->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
						$newPasswordRequest->setUserType($testedType);
					}
				}
			}
            if (!$user) {
                $message = "Cet email ne correspond à aucun compte, veuillez vérifier votre saisie.";
            } else {
                $isAlreadyRequested = false;
                $oldRequest = $newPasswordRequestRepository->findOneBy(['userId' => $user->getId(), 'userType' => $newPasswordRequest->getUserType()], ['createdAt' => 'DESC']);
                if ($oldRequest) {
                    $now = new \DateTime();
                    $interval = $now->getTimestamp() - $oldRequest->getCreatedAt()->getTimestamp();
                    $isAlreadyRequested = $interval < 1800;
                }
                if ($isAlreadyRequested) {
                    $message = "La demande de réinitialisation du mot de passe à déjà été faite, veuillez patienter avant de réessayer.";
                } else {
                    $newPasswordRequest->setUserId($user->getId());
                    $token = $tokenGenerator->generateToken();
                    $newPasswordRequest->setToken($token);

                    $entityManager->persist($newPasswordRequest);
                    $entityManager->flush();

                    $mailer->sendMail(
						$newPasswordRequest->getUserEmail(),
						$coreOptions->getSiteTitle().' - Réinitialisation du mot de passe',
						'',
						$coreOptions->getSiteTitle().' - Réinitialisation du mot de passe',
						'@AkyosCore/new_password_request/reset_password_email.html.twig',
						null,
						null,
						null,
						null,
						[
							'templateParams' => [
								'newPasswordRequest' => $newPasswordRequest
							]
						]
					);
                    $newPasswordRequest->setIsMailSent(true);

                    $entityManager->flush();
                }
            }
        }

        return $this->render('@AkyosCore/new_password_request/index.html.twig', [
            'new_password_request' => $newPasswordRequest,
            'form'                 => $form->createView(),
            'message'              => $message
        ]);
    }

    /**
     * @Route("/reset/{id}/{token}", name="_change", methods={"GET", "POST"})
     * @param int $id
     * @param string $token
     * @param NewPasswordRequestRepository $newPasswordRequestRepository
     * @param Request $request
     * @param CoreMailer $mailer
     * @param CoreOptionsRepository $coreOptionsRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @throws \Exception
     */
    public function changePassword(int $id, string $token, NewPasswordRequestRepository $newPasswordRequestRepository, Request $request, CoreMailer $mailer, CoreOptionsRepository $coreOptionsRepository, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            $coreOptions = $coreOptions[0];
        }
        $newPasswordRequest = $newPasswordRequestRepository->findOneBy(['userId' => $id, 'token' => $token], ['createdAt' => 'DESC']);
        $message = '';

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if(!$newPasswordRequest) {
            $message = 'Vous n\'avez pas l\'autorisation d\'accéder à cette page.';
        } else {
            $now = new \DateTime();
            $interval = $now->getTimestamp() - $newPasswordRequest->getCreatedAt()->getTimestamp();
            $isRequestedInTime = $interval < 1800;

            if(!$isRequestedInTime) {
                $message = 'Le délai est dépassé, veuillez effectuer une nouvelle demande.';
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $user = null;
            if (class_exists('Akyos\\CoreBundle\\Entity\\' . $newPasswordRequest->getUserType())) {
                $user = $this->getDoctrine()->getRepository('Akyos\\CoreBundle\\Entity\\' . $newPasswordRequest->getUserType())->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
            }
            if (!$user) {
                if (class_exists('App\\Entity\\' . $newPasswordRequest->getUserType())) {
                    $user = $this->getDoctrine()->getRepository('App\\Entity\\' . $newPasswordRequest->getUserType())->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
                }
            }

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $newPasswordRequest->setIsPasswordChanged(true);
            $entityManager->persist($newPasswordRequest);
            $entityManager->persist($user);
            $entityManager->flush();
            
			$mailer->sendMail(
				$newPasswordRequest->getUserEmail(),
				$coreOptions->getSiteTitle().' - Mot de passe réinitialisé',
				'',
				$coreOptions->getSiteTitle().' - Mot de passe réinitialisé',
				'@AkyosCore/new_password_request/reset_password_email.html.twig',
				null,
				null,
				null,
				null,
				[
					'templateParams' => [
						'newPasswordRequest' => $newPasswordRequest
					]
				]
			);
            $newPasswordRequest->setIsConfirmationSent(true);

            $entityManager->flush();
        }

        return $this->render('@AkyosCore/new_password_request/change_password.html.twig', [
            'new_password_request' => $newPasswordRequest,
            'form'                 => $form->createView(),
            'message'              => $message
        ]);
    }
}
