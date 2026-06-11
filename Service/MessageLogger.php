<?php

namespace Akyos\CoreBundle\Service;

use Akyos\CoreBundle\Entity\MessageLog;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class MessageLogger
{
    private readonly EntityManagerInterface $entityManager;

    private readonly SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, private readonly ErrorCatcher $catcher)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * @param $message
     * @param $error
     * @param string|null $type
     * @param bool|null $doNotFlush
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function saveLog($message = null, $error = null, ?string $type = null, ?bool $doNotFlush = null): bool
    {
        $messageLog = new MessageLog();

        if ($message) {
            $messageLog->setMessage($this->serializer->serialize($message, 'json'));
        }
        if ($error) {
            try {
                $messageLog->setError($this->serializer->serialize($error, 'json'));
            } catch (Exception) {
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
            return $this->catcher->catch($e);
        }
    }
}