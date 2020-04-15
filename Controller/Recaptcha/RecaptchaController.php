<?php

namespace Akyos\CoreBundle\Controller\Recaptcha;

use Akyos\CoreBundle\Entity\RgpdOptions;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/recaptcha", name="recaptcha_")
 */
class RecaptchaController extends AbstractController
{
    /**
     * @Route("/recaptcha-v3-verify/{action}/{token}", name="v3_verify")
     * @param string $action
     * @param string $token
     * @param Request $request
     * @param CoreOptionsRepository $coreOptionsRepository
     * @return JsonResponse
     */
    public function recaptchaV3Verify(string $action, string $token, Request $request, CoreOptionsRepository $coreOptionsRepository)
    {
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            $coreOptions = $coreOptions[0];
        }

        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_private = ($coreOptions->getRecaptchaPrivateKey() ? $coreOptions->getRecaptchaPrivateKey() : null);
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_private . '&response=' . $token);
        $recaptcha = json_decode($recaptcha);
        if($recaptcha->success) {
            if ($recaptcha->score >= 0.5) {
                return new JsonResponse(['error' => false]);
            } else {
                return new JsonResponse(['error' => true, 'message' => 'La vérification recaptcha est invalide, veuillez réessayer ultérieurement.']);
            }
        } else {
            return new JsonResponse(['error' => true, 'message' => 'La vérification recaptcha est invalide, veuillez réessayer ultérieurement: '.json_encode($recaptcha)]);
        }
    }
}
