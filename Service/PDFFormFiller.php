<?php

namespace Akyos\CoreBundle\Service;

use mikehaertl\pdftk\Pdf;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;

// Needs composer require mikehaertl/php-pdftk
class PDFFormFiller
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param $filepath
     * @param string $filename
     * @param array $fields
     * @return array
     */
    public function fillPDFForm($filepath, string $filename, array $fields): array
    {
        $filename .= '.pdf';
        $pdf = new Pdf($filepath);
        $filledPdfDir = $this->kernel->getProjectDir() . '/filledPdf';
        if (!is_dir($filledPdfDir) && !mkdir($filledPdfDir, 0755, true) && !is_dir($filledPdfDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $filledPdfDir));
        }
        $absoluteDir = $filledPdfDir . '/' . $filename;
        $absoluteFlattenDir = $filledPdfDir . '/flatten' . $filename;
        $relativeDir = '/filledPdf/' . $filename;
        $result = $pdf->fillForm($fields)->needAppearances()->saveAs($absoluteDir);

        $flattenResult = new Pdf($absoluteDir);
        $flattenResult->flatten()->saveAs($absoluteFlattenDir);

        if ($result === false) {
            return $pdf->getError();
        }

        return ['relative_dir' => $relativeDir, 'absolute_dir' => $absoluteDir, 'flattenedDir' => $absoluteFlattenDir, 'b64file' => chunk_split(base64_encode(file_get_contents($absoluteFlattenDir))),];
    }

    /**
     * @param $files
     * @param string $filename
     * @param bool $isFlatten
     * @return array
     */
    public function catFiles($files, string $filename, bool $isFlatten = true): string|array
    {
        $filename .= '.pdf';
        $pdf = new Pdf($files);
        $filledPdfDir = $this->kernel->getProjectDir() . '/filledPdf';
        if (!is_dir($filledPdfDir) && !mkdir($filledPdfDir, 0755, true) && !is_dir($filledPdfDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $filledPdfDir));
        }
        $absoluteDir = $filledPdfDir . '/' . $filename;
        $relativeDir = '/filledPdf/' . $filename;
        $result = $pdf->saveAs($absoluteDir);

        if ($isFlatten) {
            $absoluteFlattenDir = $filledPdfDir . '/flatten' . $filename;
            $flattenResult = new Pdf($absoluteDir);
            $flattenResult->flatten()->saveAs($absoluteFlattenDir);
        }

        if ($result === false) {
            return $pdf->getError();
        }

        return ['relative_dir' => $relativeDir, 'absolute_dir' => $absoluteDir, 'flattenedDir' => ($absoluteFlattenDir ?? null), 'b64file' => chunk_split(base64_encode(file_get_contents(($isFlatten ? $absoluteFlattenDir : $absoluteDir)))),];
    }

    /**
     * @param $b64File
     * @return string
     */
    public function flattenb64File($b64File): string
    {
        $filename = uniqid('', false) . '.pdf';
        $filledPdfDir = $this->kernel->getProjectDir() . '/filledPdf';
        if (!is_dir($filledPdfDir) && !mkdir($filledPdfDir, 0755, true) && !is_dir($filledPdfDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $filledPdfDir));
        }
        $absoluteFlattenDir = $filledPdfDir . '/flatten' . $filename;
        $file = file_put_contents($absoluteFlattenDir, base64_decode($b64File));
        $pdf = new Pdf($absoluteFlattenDir);
        $result = $pdf->flatten()->saveAs($absoluteFlattenDir);

        if ($result === false) {
            return $pdf->getError();
        }

        return chunk_split(base64_encode(file_get_contents($absoluteFlattenDir)));
    }

    /**
     * @param $filepath
     * @return mixed
     */
    public function flattenPDF($filepath)
    {
        $flattenResult = new Pdf($filepath);
        $flattenResult->flatten()->saveAs($filepath);

        if ($flattenResult === false) {
            return $flattenResult->getError();
        }

        return $filepath;
    }
}
