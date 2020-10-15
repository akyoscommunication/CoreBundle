<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Akyos\CoreBundle\Repository\CoreOptionsRepository")
 */
class CoreOptions
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $siteTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $favicon;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $siteLogo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $backMainColor;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasPosts;

    /**
     * @ORM\OneToOne(targetEntity="Akyos\CoreBundle\Entity\Page", cascade={"persist", "remove"})
     */
    private $homepage;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $hasArchiveEntities = [];

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $hasSingleEntities = [];

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $hasSeoEntities = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $agencyLink;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $agencyName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recaptchaPublicKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recaptchaPrivateKey;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasPostDocuments;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiteTitle(): ?string
    {
        return $this->siteTitle;
    }

    public function setSiteTitle(?string $siteTitle): self
    {
        $this->siteTitle = $siteTitle;

        return $this;
    }

    public function getFavicon(): ?string
    {
        return $this->favicon;
    }

    public function setFavicon(?string $favicon): self
    {
        $this->favicon = $favicon;

        return $this;
    }

    public function getSiteLogo(): ?string
    {
        return $this->siteLogo;
    }

    public function setSiteLogo(?string $siteLogo): self
    {
        $this->siteLogo = $siteLogo;

        return $this;
    }

    public function getBackMainColor(): ?string
    {
        return $this->backMainColor;
    }

    public function setBackMainColor(?string $backMainColor): self
    {
        $this->backMainColor = $backMainColor;

        return $this;
    }

    public function getHasPosts(): ?bool
    {
        return $this->hasPosts;
    }

    public function setHasPosts(bool $hasPosts): self
    {
        $this->hasPosts = $hasPosts;

        return $this;
    }

    public function getHomepage(): ?Page
    {
        return $this->homepage;
    }

    public function setHomepage(?Page $homepage): self
    {
        $this->homepage = $homepage;

        return $this;
    }

    public function getHasArchiveEntities(): ?array
    {
        return $this->hasArchiveEntities;
    }

    public function setHasArchiveEntities(?array $hasArchiveEntities): self
    {
        $this->hasArchiveEntities = $hasArchiveEntities;

        return $this;
    }

    public function getHasSingleEntities(): ?array
    {
        return $this->hasSingleEntities;
    }

    public function setHasSingleEntities(?array $hasSingleEntities): self
    {
        $this->hasSingleEntities = $hasSingleEntities;

        return $this;
    }

    public function getHasSeoEntities(): ?array
    {
        return $this->hasSeoEntities;
    }

    public function setHasSeoEntities(?array $hasSeoEntities): self
    {
        $this->hasSeoEntities = $hasSeoEntities;

        return $this;
    }

    public function getAgencyLink(): ?string
    {
        return $this->agencyLink;
    }

    public function setAgencyLink(string $agencyLink): self
    {
        $this->agencyLink = $agencyLink;

        return $this;
    }

    public function getAgencyName(): ?string
    {
        return $this->agencyName;
    }

    public function setAgencyName(string $agencyName): self
    {
        $this->agencyName = $agencyName;

        return $this;
    }

    public function getRecaptchaPublicKey(): ?string
    {
        return $this->recaptchaPublicKey;
    }

    public function setRecaptchaPublicKey(string $recaptchaPublicKey): self
    {
        $this->recaptchaPublicKey = $recaptchaPublicKey;

        return $this;
    }

    public function getRecaptchaPrivateKey(): ?string
    {
        return $this->recaptchaPrivateKey;
    }

    public function setRecaptchaPrivateKey(string $recaptchaPrivateKey): self
    {
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;

        return $this;
    }

    public function getHasPostDocuments(): ?bool
    {
        return $this->hasPostDocuments;
    }

    public function setHasPostDocuments(?bool $hasPostDocuments): self
    {
        $this->hasPostDocuments = $hasPostDocuments;

        return $this;
    }
}
