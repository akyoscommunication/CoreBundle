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
	
	public function fillPDFForm(string $filepath, string $filename, array $fields)
	{
		$filename = $filename . '.pdf';
		$pdf = new Pdf($filepath);
		$filledPdfDir = $this->kernel->getProjectDir() . '/filledPdf';
		if (!is_dir($filledPdfDir)) {
			mkdir($filledPdfDir, 0755, true);
		}
		$absoluteDir = $filledPdfDir . '/' . $filename;
		$relativeDir = '/filledPdf/' . $filename;
		$result = $pdf
			->fillForm($fields)
			->needAppearances()
			->flatten()
			->saveAs($absoluteDir);
		
		if ($result === false) {
			return $pdf->getError();
		}
		
		return [
			'relative_dir' => $relativeDir,
			'absolute_dir' => $absoluteDir,
			'b64file' => chunk_split(base64_encode(file_get_contents($absoluteDir))),
		];
	}
}