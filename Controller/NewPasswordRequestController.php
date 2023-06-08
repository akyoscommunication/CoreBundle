<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\NewPasswordRequest;
use Akyos\CoreBundle\Form\Type\ChangePasswordType;
use Akyos\CoreBundle\Form\Type\NewPasswordRequestType;
use Akyos\CoreBundle\Repository\NewPasswordRequestRepository;
use Akyos\CoreBundle\Service\CoreMailer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/app/new_password_request', name: 'new_password')]
class NewPasswordRequestController extends AbstractController
{
    /**
     * @param string $type
     * @param string $route
     * @param NewPasswordRequestRepository $newPasswordRequestRepository
     * @param Request $request
     * @param TokenGeneratorInterface $tokenGenerator
     * @param CoreMailer $mailer
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/{type}/{route}', name: '', methods: ['GET', 'POST'], requirements: ['route' => '.+'])]
    public function newPasswordRequest(string $type, string $route, NewPasswordRequestRepository $newPasswordRequestRepository, Request $request, TokenGeneratorInterface $tokenGenerator, CoreMailer $mailer, TranslatorInterface $translator, EntityManagerInterface $entityManager): Response
    {
        $types = explode(';', urldecode($type));
        $newPasswordRequest = new NewPasswordRequest();
        $newPasswordRequest->setUserRoute($route);
        $form = $this->createForm(NewPasswordRequestType::class, $newPasswordRequest);
        $form->handleRequest($request);
        $message = "";
        if ($form->isSubmitted() && $form->isValid()) {
            $user = null;
            foreach ($types as $testedType) {
                $testedType = implode('\\', explode('_', $testedType));
                if (class_exists('Akyos\\CmsBundle\\Entity\\' . $testedType)) {
                    $user = $entityManager->getRepository('Akyos\\CmsBundle\\Entity\\' . $testedType)->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
                    $newPasswordRequest->setUserType($testedType);
                }
                if (!$user && class_exists('App\\Entity\\' . $testedType)) {
                    $user = $entityManager->getRepository('App\\Entity\\' . $testedType)->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
                    $newPasswordRequest->setUserType($testedType);
                }
            }
            if (!$user) {
                $message = $translator->trans('no_account', [], 'new_password_request');
            } else {
                $isAlreadyRequested = false;
                $oldRequest = $newPasswordRequestRepository->findOneBy(['userId' => $user->getId(), 'userType' => $newPasswordRequest->getUserType()], ['createdAt' => 'DESC']);
                if ($oldRequest) {
                    $now = new DateTime();
                    $interval = $now->getTimestamp() - $oldRequest->getCreatedAt()->getTimestamp();
                    $isAlreadyRequested = $interval < 1800;
                }
                if ($isAlreadyRequested) {
                    $message = $translator->trans('already_requested', [], 'new_password_request');
                } else {
                    $newPasswordRequest->setUserId($user->getId());
                    $token = $tokenGenerator->generateToken();
                    $newPasswordRequest->setToken($token);

                    $entityManager->persist($newPasswordRequest);
                    $entityManager->flush();

                    $mailer->sendMail($newPasswordRequest->getUserEmail(), $this->getParameter('site_name') . ' - Réinitialisation du mot de passe', '', $this->getParameter('site_name') . ' - Réinitialisation du mot de passe', '@AkyosCore/new_password_request/reset_password_email.html.twig', null, null, null, null, ['templateParams' => ['newPasswordRequest' => $newPasswordRequest]]);
                    $newPasswordRequest->setIsMailSent(true);

                    $entityManager->flush();
                }
            }
        }
        return $this->render('@AkyosCore/new_password_request/index.html.twig', ['new_password_request' => $newPasswordRequest, 'form' => $form->createView(), 'message' => $message]);
    }

    /**
     * @param int $id
     * @param string $token
     * @param NewPasswordRequestRepository $newPasswordRequestRepository
     * @param Request $request
     * @param CoreMailer $mailer
     * @param TranslatorInterface $translator
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '/reset/{id}/{token}', name: '_change', methods: ['GET', 'POST'])]
    public function changePassword(int $id, string $token, NewPasswordRequestRepository $newPasswordRequestRepository, Request $request, CoreMailer $mailer, TranslatorInterface $translator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $newPasswordRequest = $newPasswordRequestRepository->findOneBy(['userId' => $id, 'token' => $token], ['createdAt' => 'DESC']);
        $message = '';
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        if (!$newPasswordRequest) {
            $message = $translator->trans('unauthorized', [], 'new_password_request');
        } else {
            $now = new DateTime();
            $interval = $now->getTimestamp() - $newPasswordRequest->getCreatedAt()->getTimestamp();
            $isRequestedInTime = $interval < 1800;

            if (!$isRequestedInTime) {
                $message = $translator->trans('deadline_past', [], 'new_password_request');
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $user = null;
            if (class_exists('Akyos\\CmsBundle\\Entity\\' . $newPasswordRequest->getUserType())) {
                $user = $entityManager->getRepository('Akyos\\CmsBundle\\Entity\\' . $newPasswordRequest->getUserType())->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
            }
            if (!$user && class_exists('App\\Entity\\' . $newPasswordRequest->getUserType())) {
                $user = $entityManager->getRepository('App\\Entity\\' . $newPasswordRequest->getUserType())->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));

            $newPasswordRequest->setIsPasswordChanged(true);
            $entityManager->persist($newPasswordRequest);
            $entityManager->persist($user);
            $entityManager->flush();

            $mailer->sendMail($newPasswordRequest->getUserEmail(), $this->getParameter('site_name') . ' - Mot de passe réinitialisé', '', $this->getParameter('site_name') . ' - Mot de passe réinitialisé', '@AkyosCore/new_password_request/changed_password_email.html.twig', null, null, null, null, ['templateParams' => ['newPasswordRequest' => $newPasswordRequest]]);
            $newPasswordRequest->setIsConfirmationSent(true);

            $entityManager->flush();
        }
        return $this->render('@AkyosCore/new_password_request/change_password.html.twig', ['new_password_request' => $newPasswordRequest, 'form' => $form->createView(), 'message' => $message]);
    }
}
