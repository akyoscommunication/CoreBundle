<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/recaptcha", name="recaptcha_")
 */
class RecaptchaController extends AbstractController
{
    /**
     * @Route("/recaptcha-v3-verify/{action}/{token}", name="v3_verify")
     * @param string $token
     * @param CoreOptionsRepository $coreOptionsRepository
     * @return JsonResponse
     * @throws JsonException
     */
	public function recaptchaV3Verify(string $token, CoreOptionsRepository $coreOptionsRepository): JsonResponse
    {
		$coreOptions = $coreOptionsRepository->findAll();
		if ($coreOptions) {
			$coreOptions = $coreOptions[0];
		}

		$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
		$recaptcha_private = ($coreOptions->getRecaptchaPrivateKey() ?: null);
		$recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_private . '&response=' . $token);
		$recaptcha = json_decode($recaptcha, true, 512, JSON_THROW_ON_ERROR);
		if ($recaptcha->success && $recaptcha->score >= 0.8) {
            return new JsonResponse(['error' => false]);
        }
		return new JsonResponse(['error' => true, 'message' => 'La vérification recaptcha est invalide, ou le délai est expiré, veuillez réessayer l\'envoi du formulaire.']);
	}
}
