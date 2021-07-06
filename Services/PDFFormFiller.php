<?php

namespace Akyos\CoreBundle\Services;

use mikehaertl\pdftk\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;

// Needs composer require mikehaertl/php-pdftk
class PDFFormFiller
{
	
	private $kernel;
	
	public function __construct(KernelInterface $kernel)
	{
		$this->kernel = $kernel;
	}
	
	public function fillPDFForm($filepath, string $filename, array $fields)
	{
		$filename = $filename . '.pdf';
		$pdf = new Pdf($filepath);
		$filledPdfDir = $this->kernel->getProjectDir() . '/filledPdf';
		if (!is_dir($filledPdfDir)) {
			mkdir($filledPdfDir, 0755, true);
		}
		$absoluteDir = $filledPdfDir . '/' . $filename;
		$absoluteFlattenDir = $filledPdfDir . '/flatten'.$filename;
		$relativeDir = '/filledPdf/' . $filename;
		$result = $pdf
			->fillForm($fields)
			->needAppearances()
			->saveAs($absoluteDir);
		
		$flattenResult = new Pdf($absoluteDir);
		$flattenResult->flatten()->saveAs($absoluteFlattenDir);
		
		if ($result === false) {
			return $pdf->getError();
		}
		
		return [
			'relative_dir' => $relativeDir,
			'absolute_dir' => $absoluteDir,
			'flattenedDir' => $absoluteFlattenDir,
			'b64file' => chunk_split(base64_encode(file_get_contents($absoluteFlattenDir))),
		];
	}
	
	public function catFiles($files, string $filename, bool $isFlatten = true)
	{
		$filename = $filename . '.pdf';
		$pdf = new Pdf($files);
		$filledPdfDir = $this->kernel->getProjectDir() . '/filledPdf';
		if (!is_dir($filledPdfDir)) {
			mkdir($filledPdfDir, 0755, true);
		}
		$absoluteDir = $filledPdfDir . '/' . $filename;
		$relativeDir = '/filledPdf/' . $filename;
		$result = $pdf
			->saveAs($absoluteDir);

		if ($isFlatten) {
            $absoluteFlattenDir = $filledPdfDir . '/flatten'.$filename;
            $flattenResult = new Pdf($absoluteDir);
            $flattenResult->flatten()->saveAs($absoluteFlattenDir);
        }

		if ($result === false) {
			return $pdf->getError();
		}
		
		return [
			'relative_dir' => $relativeDir,
			'absolute_dir' => $absoluteDir,
			'flattenedDir' => (isset($absoluteFlattenDir) ? $absoluteFlattenDir : null),
			'b64file' => chunk_split(base64_encode(file_get_contents(($isFlatten ? $absoluteFlattenDir : $absoluteDir)))),
		];
	}
	
	public function flattenb64File($b64File): string
	{
		$filename = uniqid() . '.pdf';
		$filledPdfDir = $this->kernel->getProjectDir() . '/filledPdf';
		if (!is_dir($filledPdfDir)) {
			mkdir($filledPdfDir, 0755, true);
		}
		$absoluteFlattenDir = $filledPdfDir . '/flatten'.$filename;
		$file = file_put_contents($absoluteFlattenDir, base64_decode($b64File));
		$pdf = new Pdf($absoluteFlattenDir);
		$result = $pdf
			->flatten()
			->saveAs($absoluteFlattenDir);
		
		if ($result === false) {
			return $pdf->getError();
		}
		
		return chunk_split(base64_encode(file_get_contents($absoluteFlattenDir)));
	}
}