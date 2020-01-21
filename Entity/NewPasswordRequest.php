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
	
	const ENTITY_SLUG = "password-requests";
	
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getUserRoute(): ?string
    {
        return $this->userRoute;
    }

    public function setUserRoute(string $userRoute): self
    {
        $this->userRoute = $userRoute;

        return $this;
    }

    public function getIsMailSent(): ?bool
    {
        return $this->isMailSent;
    }

    public function setIsMailSent(?bool $isMailSent): self
    {
        $this->isMailSent = $isMailSent;

        return $this;
    }

    public function getIsPasswordChanged(): ?bool
    {
        return $this->isPasswordChanged;
    }

    public function setIsPasswordChanged(?bool $isPasswordChanged): self
    {
        $this->isPasswordChanged = $isPasswordChanged;

        return $this;
    }

    public function getIsConfirmationSent(): ?bool
    {
        return $this->isConfirmationSent;
    }

    public function setIsConfirmationSent(?bool $isConfirmationSent): self
    {
        $this->isConfirmationSent = $isConfirmationSent;

        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): self
    {
        $this->userEmail = $userEmail;

        return $this;
    }
}
