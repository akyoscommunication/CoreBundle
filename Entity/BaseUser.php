<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MappedSuperclass
 * @UniqueEntity("email", message="email_already_used")
 */
class BaseUser implements UserInterface, PasswordAuthenticatedUserInterface
{
	use TimestampableEntity;
	
	/**
	 * @ORM\Column(type="string", length=180, unique=true)
	 * @Assert\NotBlank()
	 * @Assert\Email(
	 *     message = "The email '{{ value }}' is not a valid email."
	 * )
	 */
	protected $email;
	
	/**
	 * @ORM\Column(type="json")
	 */
	protected $roles = [];
	
	/**
	 * @var string The hashed password
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank()
	 */
	protected $password;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $image;

    /**
     * @return string|null
     */
	public function getEmail(): ?string
	{
		return $this->email;
	}

    /**
     * @param string $email
     * @return $this
     */
	public function setEmail(string $email): self
	{
		$this->email = $email;
		
		return $this;
	}

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @return array|string[]
     */
	public function getRoles(): array
	{
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';
		
		return array_unique($roles);
	}

    /**
     * @return array
     */
	public function getRolesDisplay(): array
	{
		$roles = $this->roles;
		
		return array_unique($roles);
	}

    /**
     * @param array $roles
     * @return $this
     */
	public function setRoles(array $roles): self
	{
		$this->roles = $roles;
		
		return $this;
	}

    /**
     * @return string
     */
	public function getPassword(): string
	{
		return (string)$this->password;
	}

    /**
     * @param $password
     * @return $this
     */
	public function setPassword($password): self
	{
		$this->password = $password;
		
		return $this;
	}

    /**
     * @return string|void|null
     */
	public function getSalt()
	{
		// not needed when using the "bcrypt" algorithm in security.yaml
	}

    /**
     * @return void
     */
	public function eraseCredentials()
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

    /**
     * @return string|null
     */
	public function getImage(): ?string
	{
		return $this->image;
	}

    /**
     * @param string|null $image
     * @return $this
     */
	public function setImage(?string $image): self
	{
		$this->image = $image;
		
		return $this;
	}
}
