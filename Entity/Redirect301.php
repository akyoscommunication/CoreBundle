<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="Akyos\CoreBundle\Repository\Redirect301Repository")
 */
class Redirect301
{
    use TimestampableEntity;

    const ENTITY_SLUG = "redirect_301";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $oldSlug;

    /**
     * @ORM\Column(type="integer")
     */
    private $objectId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $objectType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $newSlug;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOldSlug(): ?string
    {
        return $this->oldSlug;
    }

    public function setOldSlug(string $oldSlug): self
    {
        $this->oldSlug = $oldSlug;

        return $this;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setObjectId(int $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getObjectType(): ?string
    {
        return $this->objectType;
    }

    public function setObjectType(string $objectType): self
    {
        $this->objectType = $objectType;

        return $this;
    }

    public function getNewSlug(): ?string
    {
        return $this->newSlug;
    }

    public function setNewSlug(string $newSlug): self
    {
        $this->newSlug = $newSlug;

        return $this;
    }
}
