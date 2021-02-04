<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Entity\MessageLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class MessageLogger {
	
	private $entityManager;
	private $serializer;
	
	public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
	{
		$this->entityManager = $entityManager;
		$this->serializer = $serializer;
	}
	
	public function saveLog($message = null, $error = null, String $type = null, bool $doNotFlush = null)
	{
		$messageLog = new MessageLog();
		
		if($message) {
			$messageLog->setMessage($this->serializer->serialize($message, 'json'));
		}
//		if($error) {
//			$messageLog->setError($this->serializer->serialize($error, 'json'));
//		}
		if($type) {
			$messageLog->setType($type);
		}
		
		try {
			$this->entityManager->persist($messageLog);
			if(!$doNotFlush) {
				$this->entityManager->flush();
			}
			return true;
		} catch (\Exception $e) {
			return $e;
		}
	}
}