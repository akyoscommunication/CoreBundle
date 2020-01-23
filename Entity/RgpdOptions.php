<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Akyos\CoreBundle\Repository\RgpdOptionsRepository")
 */
class RgpdOptions
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $siteName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contactMail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $analyticsCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tagManagerCode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasYoutubeVideos;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $soppCustomerId;

    /**
     * @ORM\OneToOne(targetEntity="Akyos\CoreBundle\Entity\Page", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $contactPage;

    /**
     * @ORM\OneToOne(targetEntity="Akyos\CoreBundle\Entity\Page", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $policyPage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiteName(): ?string
    {
        return $this->siteName;
    }

    public function setSiteName(string $siteName): self
    {
        $this->siteName = $siteName;

        return $this;
    }

    public function getContactMail(): ?string
    {
        return $this->contactMail;
    }

    public function setContactMail(string $contactMail): self
    {
        $this->contactMail = $contactMail;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAnalyticsCode(): ?string
    {
        return $this->analyticsCode;
    }

    public function setAnalyticsCode(?string $analyticsCode): self
    {
        $this->analyticsCode = $analyticsCode;

        return $this;
    }

    public function getTagManagerCode(): ?string
    {
        return $this->tagManagerCode;
    }

    public function setTagManagerCode(?string $tagManagerCode): self
    {
        $this->tagManagerCode = $tagManagerCode;

        return $this;
    }

    public function getHasYoutubeVideos(): ?bool
    {
        return $this->hasYoutubeVideos;
    }

    public function setHasYoutubeVideos(?bool $hasYoutubeVideos): self
    {
        $this->hasYoutubeVideos = $hasYoutubeVideos;

        return $this;
    }

    public function getSoppCustomerId(): ?string
    {
        return $this->soppCustomerId;
    }

    public function setSoppCustomerId(?string $soppCustomerId): self
    {
        $this->soppCustomerId = $soppCustomerId;

        return $this;
    }

    public function getContactPage(): ?Page
    {
        return $this->contactPage;
    }

    public function setContactPage(Page $contactPage): self
    {
        $this->contactPage = $contactPage;

        return $this;
    }

    public function getPolicyPage(): ?Page
    {
        return $this->policyPage;
    }

    public function setPolicyPage(Page $policyPage): self
    {
        $this->policyPage = $policyPage;

        return $this;
    }
}
