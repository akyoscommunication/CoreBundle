<?php

namespace Akyos\CoreBundle\Controller\Recaptcha;

use Akyos\CoreBundle\Entity\MenuArea;
use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Repository\MenuItemRepository;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/recaptcha", name="recaptcha_")
 */
class RecaptchaController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('@AkyosCore/core_bundle/recaptcha.html.twig', [
            'title' => 'Tableau de Bord',
        ]);
    }

    /**
     * @Route("/recaptcha-v3-verify", name="v3_verify")
     * @param Request $request
     */
    public function recaptchaV3Verify(Request $request)
    {
        $recaptcha = new ReCaptcha($request->get('token'));
        $res = $recaptcha
            ->setExpectedAction('verify')
            ->verify($request->get('token'));

        if ($res->isSuccess()) {

        } else {
            dd($res->getErrorCodes());
        }
    }
}
