<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="Akyos\CoreBundle\Repository\NewPasswordRequestRepository")
 */
class NewPasswordRequest
{
	use TimestampableEntity;
	
	public const ENTITY_SLUG = "password-requests";
	
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	private $userId;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $userType;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $token;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $userRoute;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $isMailSent;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $isPasswordChanged;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $isConfirmationSent;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $userEmail;

    /**
     * @return int|null
     */
	public function getId(): ?int
	{
		return $this->id;
	}

    /**
     * @return int|null
     */
	public function getUserId(): ?int
	{
		return $this->userId;
	}

    /**
     * @param int $userId
     * @return $this
     */
	public function setUserId(int $userId): self
	{
		$this->userId = $userId;
		
		return $this;
	}

    /**
     * @return string|null
     */
	public function getUserType(): ?string
	{
		return $this->userType;
	}

    /**
     * @param string $userType
     * @return $this
     */
	public function setUserType(string $userType): self
	{
		$this->userType = $userType;
		
		return $this;
	}

    /**
     * @return string|null
     */
	public function getToken(): ?string
	{
		return $this->token;
	}

    /**
     * @param string $token
     * @return $this
     */
	public function setToken(string $token): self
	{
		$this->token = $token;
		
		return $this;
	}

    /**
     * @return string|null
     */
	public function getUserRoute(): ?string
	{
		return $this->userRoute;
	}

    /**
     * @param string $userRoute
     * @return $this
     */
	public function setUserRoute(string $userRoute): self
	{
		$this->userRoute = $userRoute;
		
		return $this;
	}

    /**
     * @return bool|null
     */
	public function getIsMailSent(): ?bool
	{
		return $this->isMailSent;
	}

    /**
     * @param bool|null $isMailSent
     * @return $this
     */
	public function setIsMailSent(?bool $isMailSent): self
	{
		$this->isMailSent = $isMailSent;
		
		return $this;
	}

    /**
     * @return bool|null
     */
	public function getIsPasswordChanged(): ?bool
	{
		return $this->isPasswordChanged;
	}

    /**
     * @param bool|null $isPasswordChanged
     * @return $this
     */
	public function setIsPasswordChanged(?bool $isPasswordChanged): self
	{
		$this->isPasswordChanged = $isPasswordChanged;
		
		return $this;
	}

    /**
     * @return bool|null
     */
	public function getIsConfirmationSent(): ?bool
	{
		return $this->isConfirmationSent;
	}

    /**
     * @param bool|null $isConfirmationSent
     * @return $this
     */
	public function setIsConfirmationSent(?bool $isConfirmationSent): self
	{
		$this->isConfirmationSent = $isConfirmationSent;
		
		return $this;
	}

    /**
     * @return string|null
     */
	public function getUserEmail(): ?string
	{
		return $this->userEmail;
	}

    /**
     * @param string $userEmail
     * @return $this
     */
	public function setUserEmail(string $userEmail): self
	{
		$this->userEmail = $userEmail;
		
		return $this;
	}
}
