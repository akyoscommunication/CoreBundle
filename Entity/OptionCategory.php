<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="Akyos\CoreBundle\Repository\OptionCategoryRepository")
 */
class OptionCategory
{
	use TimestampableEntity;
	
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $title;
	
	/**
	 * @Gedmo\Slug(fields={"title"})
	 * @ORM\Column(type="string", length=255)
	 */
	private $slug;
	
	/**
	 * @ORM\OneToMany(targetEntity="Akyos\CoreBundle\Entity\Option", mappedBy="optionCategory")
	 */
	private $options;
	
	public function __construct()
	{
		$this->options = new ArrayCollection();
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
	
	public function getSlug(): ?string
	{
		return $this->slug;
	}
	
	public function setSlug(string $slug): self
	{
		$this->slug = $slug;
		
		return $this;
	}
	
	/**
	 * @return Collection|Option[]
	 */
	public function getOptions(): Collection
	{
		return $this->options;
	}
	
	public function addOption(Option $option): self
	{
		if (!$this->options->contains($option)) {
			$this->options[] = $option;
			$option->setOptionCategory($this);
		}
		
		return $this;
	}
	
	public function removeOption(Option $option): self
	{
		if ($this->options->contains($option)) {
			$this->options->removeElement($option);
			// set the owning side to null (unless already changed)
			if ($option->getOptionCategory() === $this) {
				$option->setOptionCategory(null);
			}
		}
		
		return $this;
	}
	
	public function __toString()
	{
		return $this->title;
	}
}
