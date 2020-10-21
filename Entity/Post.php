<?php

namespace Akyos\CoreBundle\Entity;

use Akyos\CoreBundle\Annotations\SlugRedirect;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="Akyos\CoreBundle\Repository\PostRepository")
 */
class Post implements Translatable
{
    use TimestampableEntity;

    const ENTITY_SLUG = "articles";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Translatable
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @SlugRedirect
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=999999999999999999, nullable=true)
     * @Gedmo\Translatable
     */
    private $content;

    /**
     * @ORM\Column(type="boolean")
     * @Gedmo\Translatable
     */
    private $published;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    private $thumbnail;

    /**
     * @ORM\ManyToMany(targetEntity="Akyos\CoreBundle\Entity\PostCategory", mappedBy="posts")
     */
    private $postCategories;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    private $thumbnailArchive;

    /**
     * @ORM\OneToMany(targetEntity=PostDocument::class, mappedBy="post")
     */
    private $postDocuments;

    public function __construct()
    {
        $this->postCategories = new ArrayCollection();
        $this->postDocuments = new ArrayCollection();
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

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return Collection|PostCategory[]
     */
    public function getPostCategories(): Collection
    {
        return $this->postCategories;
    }

    public function addPostCategory(PostCategory $postCategory): self
    {
        if (!$this->postCategories->contains($postCategory)) {
            $this->postCategories[] = $postCategory;
            $postCategory->addPost($this);
        }

        return $this;
    }

    public function removePostCategory(PostCategory $postCategory): self
    {
        if ($this->postCategories->contains($postCategory)) {
            $this->postCategories->removeElement($postCategory);
            $postCategory->removePost($this);
        }

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getThumbnailArchive(): ?string
    {
        return $this->thumbnailArchive;
    }

    public function setThumbnailArchive(?string $thumbnailArchive): self
    {
        $this->thumbnailArchive = $thumbnailArchive;

        return $this;
    }

    /**
     * @return Collection|PostDocument[]
     */
    public function getPostDocuments(): Collection
    {
        return $this->postDocuments;
    }

    public function addPostDocument(PostDocument $postDocument): self
    {
        if (!$this->postDocuments->contains($postDocument)) {
            $this->postDocuments[] = $postDocument;
            $postDocument->setPost($this);
        }

        return $this;
    }

    public function removePostDocument(PostDocument $postDocument): self
    {
        if ($this->postDocuments->contains($postDocument)) {
            $this->postDocuments->removeElement($postDocument);
            // set the owning side to null (unless already changed)
            if ($postDocument->getPost() === $this) {
                $postDocument->setPost(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string)$this->title;
    }
}
