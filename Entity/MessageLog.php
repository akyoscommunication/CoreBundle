<?php

namespace Akyos\CoreBundle\Entity;

use Akyos\CoreBundle\Repository\MessageLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=MessageLogRepository::class)
 */
class MessageLog
{
	use TimestampableEntity;

    public const ENTITY_SLUG = "message-logs";
	
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $message;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $error;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $type;

    /**
     * @return int|null
     */
	public function getId(): ?int
	{
		return $this->id;
	}

    /**
     * @return string|null
     */
	public function getMessage(): ?string
	{
		return $this->message;
	}

    /**
     * @param string|null $message
     * @return $this
     */
	public function setMessage(?string $message): self
	{
		$this->message = $message;
		
		return $this;
	}

    /**
     * @return string|null
     */
	public function getError(): ?string
	{
		return $this->error;
	}

    /**
     * @param string|null $error
     * @return $this
     */
	public function setError(?string $error): self
	{
		$this->error = $error;
		
		return $this;
	}

    /**
     * @return string|null
     */
	public function getType(): ?string
	{
		return $this->type;
	}

    /**
     * @param string|null $type
     * @return $this
     */
	public function setType(?string $type): self
	{
		$this->type = $type;
		
		return $this;
	}
}
