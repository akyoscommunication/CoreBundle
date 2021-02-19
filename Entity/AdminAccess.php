<?php

namespace Akyos\CoreBundle\Entity;

use Akyos\CoreBundle\Repository\AdminAccessRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=AdminAccessRepository::class)
 */
class AdminAccess
{
	use TimestampableEntity;
	
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;
	
	/**
	 * @Gedmo\Slug(fields={"name"})
	 * @ORM\Column(type="string", length=255)
	 */
	private $slug;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $isLocked;
	
	/**
	 * @ORM\Column(type="simple_array", nullable=true)
	 */
	private $roles = [];
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getName(): ?string
	{
		return $this->name;
	}
	
	public function setName(string $name): self
	{
		$this->name = $name;
		
		return $this;
	}
	
	public function getSlug(): ?string
	{
		return $this->slug;
	}
	
	public function setSlug(string $slug): self
	{
		$this->slug = $slug;
		
		return $this;
	}
	
	public function getIsLocked(): ?bool
	{
		return $this->isLocked;
	}
	
	public function setIsLocked(?bool $isLocked): self
	{
		$this->isLocked = $isLocked;
		
		return $this;
	}
	
	public function getRoles(): ?array
	{
		return $this->roles;
	}
	
	public function setRoles(?array $roles): self
	{
		$this->roles = $roles;
		
		return $this;
	}
}
