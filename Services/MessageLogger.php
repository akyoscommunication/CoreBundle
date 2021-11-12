<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Entity\MessageLog;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class MessageLogger
{
	private EntityManagerInterface $entityManager;
	private SerializerInterface $serializer;
	
	public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
	{
		$this->entityManager = $entityManager;
		$this->serializer = $serializer;
	}
	
	public function saveLog($message = null, $error = null, string $type = null, bool $doNotFlush = null)
	{
		$messageLog = new MessageLog();
		
		if ($message) {
			$messageLog->setMessage($this->serializer->serialize($message, 'json'));
		}
		if($error) {
            try {
                $messageLog->setError($this->serializer->serialize($error, 'json'));
            } catch (Exception $e) {
//                dd($e);
            }
		}
		if ($type) {
			$messageLog->setType($type);
		}
		
		try {
			$this->entityManager->persist($messageLog);
			if (!$doNotFlush) {
				$this->entityManager->flush();
			}
			return true;
		} catch (Exception $e) {
			return $e;
		}
	}
}