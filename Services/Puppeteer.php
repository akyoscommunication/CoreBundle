<?php

namespace Akyos\CoreBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class Puppeteer
{
    private KernelInterface $kernel;
    private ?Request $request;

    public function __construct(KernelInterface $kernel, RequestStack $requestStack)
    {
        $this->kernel = $kernel;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param $fileName
     * @param $path
     * @param bool $dl
     * @param false $pathOutput
     * @param string $margin
     * @return false|string|Response
     */
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
        }

        if ($pathOutput) {
            return $output;
        }

        return $content;
    }
}
