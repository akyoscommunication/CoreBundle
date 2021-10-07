<?php

namespace Akyos\CoreBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class Puppeteer
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var KernelInterface */
    private $kernel;
    private $request;

    public function __construct(EntityManagerInterface $em, KernelInterface $kernel, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->kernel = $kernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function generatePDF($fileName, $path, $dl = true, $pathOutput = false, $margin = '0')
    {
        $linkTo = strtok($path, '?');
        $output = $this->kernel->getProjectDir().'/documents/'.$fileName;
        $pup = $this->kernel->getProjectDir().'/puppeteer/generate.js';

        shell_exec('node '.$pup.' '.$linkTo.' '.$output.' '.$this->request->cookies->get('PHPSESSID').' '.$this->request->getSchemeAndHttpHost().' '.$margin);

        $content = file_get_contents($output);
        if ($dl) {
            $response = new Response();
            $response->headers->set('Content-Type', 'mime/type');
            $response->headers->set('Content-Disposition', 'attachment;filename='.$fileName);
            $response->setContent($content);
            return $response;
        } else if ($pathOutput) {
            return $output;
        } else {
            return $content;
        }
    }
}
