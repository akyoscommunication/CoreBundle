<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Akyos\CoreBundle\Annotations\SlugRedirect;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="Akyos\CoreBundle\Repository\PageRepository")
 */
class Page implements Translatable
{
    use TimestampableEntity;

    const ENTITY_SLUG = "pages";

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
     * @ORM\Column(type="boolean")
     * @Gedmo\Translatable
     */
    private $published;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    private $template;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\Translatable
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=999999999999999999, nullable=true)
     * @Gedmo\Translatable
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    private $thumbnail;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * @ORM\Column(type="datetime")
     */
    private $publishedAt;

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

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function __toString()
    {
        return (string)$this->title;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
