<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MappedSuperclass
 * @UniqueEntity("email", message="email_already_used")
 */
class BaseUser implements UserInterface
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
	
	public function getEmail(): ?string
	{
		return $this->email;
	}
	
	public function setEmail(string $email): self
	{
		$this->email = $email;
		
		return $this;
	}
	
	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUsername(): string
	{
		return (string)$this->email;
	}

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }
	
	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';
		
		return array_unique($roles);
	}
	
	/**
	 * @see UserInterface
	 */
	public function getRolesDisplay(): array
	{
		$roles = $this->roles;
		
		return array_unique($roles);
	}
	
	public function setRoles(array $roles): self
	{
		$this->roles = $roles;
		
		return $this;
	}
	
	/**
	 * @see UserInterface
	 */
	public function getPassword(): string
	{
		return (string)$this->password;
	}
	
	public function setPassword($password): self
	{
		$this->password = $password;
		
		return $this;
	}
	
	/**
	 * @see UserInterface
	 */
	public function getSalt()
	{
		// not needed when using the "bcrypt" algorithm in security.yaml
	}
	
	/**
	 * @see UserInterface
	 */
	public function eraseCredentials()
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}
	
	public function getImage(): ?string
	{
		return $this->image;
	}
	
	public function setImage(?string $image): self
	{
		$this->image = $image;
		
		return $this;
	}
}
