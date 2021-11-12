<?php

namespace Akyos\CoreBundle\Entity;

use Akyos\CoreBundle\Annotations\SlugRedirect;
use Akyos\CoreBundle\Repository\PostTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=PostTagRepository::class)
 */
class PostTag
{
	use TimestampableEntity;
	
	public const ENTITY_SLUG = "etiquette-article";
	
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 * @Gedmo\Translatable
	 */
	private $title;
	
	/**
	 * @Gedmo\Slug(fields={"title"})
	 * @Gedmo\Translatable
	 * @SlugRedirect
	 * @ORM\Column(type="string", length=255)
	 */
	private $slug;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $content;
	
	/**
	 * @ORM\ManyToMany(targetEntity=Post::class, inversedBy="postTags")
	 */
	private $posts;
	
	public function __construct()
	{
		$this->posts = new ArrayCollection();
	}
	
	public function __toString(): string
    {
		return (string)$this->getTitle();
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
	
	public function getContent(): ?string
	{
		return $this->content;
	}
	
	public function setContent(?string $content): self
	{
		$this->content = $content;
		
		return $this;
	}
	
	/**
	 * @return Collection|Post[]
	 */
	public function getPosts(): Collection
	{
		return $this->posts;
	}
	
	public function addPost(Post $post): self
	{
		if (!$this->posts->contains($post)) {
			$this->posts[] = $post;
		}
		
		return $this;
	}
	
	public function removePost(Post $post): self
	{
		$this->posts->removeElement($post);
		
		return $this;
	}
}
