<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\NewPasswordRequest;
use Akyos\CoreBundle\Form\NewPasswordRequestType;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\NewPasswordRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     * @param \Swift_Mailer $mailer
     * @param RequestStack $requestStack
     * @param CoreOptionsRepository $coreOptionsRepository
     * @return Response
     * @throws \Exception
     */
    public function newPasswordRequest(string $type, string $route, NewPasswordRequestRepository $newPasswordRequestRepository, Request $request, TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mailer, RequestStack $requestStack, CoreOptionsRepository $coreOptionsRepository): Response
    {
        $coreOptions = $coreOptionsRepository->findAll()[0];

        $newPasswordRequest = new NewPasswordRequest();
        $newPasswordRequest->setUserType($type);
        $newPasswordRequest->setUserRoute($route);

        $form = $this->createForm(NewPasswordRequestType::class, $newPasswordRequest);
        $form->handleRequest($request);

        $message = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $user = null;
            if (class_exists('Akyos\\CoreBundle\\Entity\\' . $type)) {
                $user = $this->getDoctrine()->getRepository('Akyos\\CoreBundle\\Entity\\' . $type)->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
            }
            if (!$user) {
                if (class_exists('App\\Entity\\' . $type)) {
                    $user = $this->getDoctrine()->getRepository('App\\Entity\\' . $type)->findOneBy(['email' => $newPasswordRequest->getUserEmail()]);
                }
            }
            if (!$user) {
                $message = "Cet email ne correspond à aucun compte, veuillez vérifier votre saisie.";
            } else {
                $isAlreadyRequested = false;
                $oldRequest = $newPasswordRequestRepository->findOneBy(['userId' => $user->getId(), 'userType' => $type], ['createdAt' => 'DESC']);
                if ($oldRequest) {
                    $now = new \DateTime();
                    $interval = $now->getTimestamp() - $oldRequest->getCreatedAt()->getTimestamp();
                    $isAlreadyRequested = $interval < 1800 ? true : false;
                }
                if ($isAlreadyRequested) {
                    $message = "La demande de réinitialisation du mot de passe à déjà été faite, veuillez patienter avant de réessayer.";
                } else {
                    $newPasswordRequest->setUserId($user->getId());
                    $token = $tokenGenerator->generateToken();
                    $newPasswordRequest->setToken($token);

                    $resetPasswordEmail = new \Swift_Message($coreOptions->getSiteTitle().' - Réinitialisation du mot de passe');
                    $resetPasswordEmail
                        ->setFrom('noreply@' . $requestStack->getCurrentRequest()->getHost())
//						->setTo($newPasswordRequest->getUserEmail())
                        ->setTo('thomas.sebert.akyos@gmail.com')
                        ->setBody($this->renderView('@AkyosCore/new_password_request/reset_password_email.html.twig', [
                            'newPasswordRequest' => $newPasswordRequest,
                        ]), 'text/html');
                    $mailer->send($resetPasswordEmail);
                    $newPasswordRequest->setIsMailSent(true);

                    $entityManager->persist($newPasswordRequest);
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
     * @Route("/{token}", name="_change", methods={"GET", "POST"})
     * @param string $token
     * @param NewPasswordRequestRepository $newPasswordRequestRepository
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @param RequestStack $requestStack
     * @return Response
     * @throws \Exception
     */
    public function changePassword(string $token, NewPasswordRequestRepository $newPasswordRequestRepository, Request $request, \Swift_Mailer $mailer, RequestStack $requestStack): Response
    {
        $newPasswordRequest = $newPasswordRequestRepository->findOneBy(['token' => $token]);

        return $this->render('@AkyosCore/new_password_request/index.html.twig', []);
    }
}
