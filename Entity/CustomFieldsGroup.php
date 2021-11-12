<?php

namespace Akyos\CoreBundle\Entity;

use Akyos\CoreBundle\Repository\CustomFieldsGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CustomFieldsGroupRepository::class)
 */
class CustomFieldsGroup
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $title;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $entity;
	
	/**
	 * @ORM\OneToMany(targetEntity=CustomField::class, mappedBy="customFieldGroup", orphanRemoval=true, cascade={"persist"})
	 */
	private $customFields;
	
	public function __construct()
	{
		$this->customFields = new ArrayCollection();
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getTitle(): ?string
	{
		return $this->title;
	}
	
	public function setTitle(string $title): self
	{
		$this->title = $title;
		
		return $this;
	}
	
	public function getEntity(): ?string
	{
		return $this->entity;
	}
	
	public function setEntity(?string $entity): self
	{
		$this->entity = $entity;
		
		return $this;
	}
	
	/**
	 * @return Collection|CustomField[]
	 */
	public function getCustomFields(): Collection
	{
		return $this->customFields;
	}
	
	public function addCustomField(CustomField $customField): self
	{
		if (!$this->customFields->contains($customField)) {
			$this->customFields[] = $customField;
			$customField->setCustomFieldGroup($this);
		}
		
		return $this;
	}
	
	public function removeCustomField(CustomField $customField): self
	{
        // set the owning side to null (unless already changed)
        if ($this->customFields->removeElement($customField) && $customField->getCustomFieldGroup() === $this) {
            $customField->setCustomFieldGroup(null);
        }
		
		return $this;
	}
}
