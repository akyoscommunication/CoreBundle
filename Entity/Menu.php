<?php

namespace Akyos\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="Akyos\CoreBundle\Repository\MenuRepository")
 */
class Menu
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
     * @ORM\OneToMany(targetEntity="Akyos\CoreBundle\Entity\MenuItem", mappedBy="menu", orphanRemoval=true, cascade={"persist"})
     * @OrderBy({"position" = "ASC"})
     */
    private $menuItems;

    /**
     * @ORM\OneToOne(targetEntity="Akyos\CoreBundle\Entity\MenuArea", mappedBy="Menu", cascade={"persist"})
     */
    private $menuArea;


    public function __construct()
    {
        $this->menuItems = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
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
     * @return Collection|MenuItem[]
     */
    public function getMenuItems(): Collection
    {
        return $this->menuItems;
    }

    public function addMenuItem(MenuItem $menuItem): self
    {
        if (!$this->menuItems->contains($menuItem)) {
            $this->menuItems[] = $menuItem;
            $menuItem->setMenu($this);
        }

        return $this;
    }

    public function removeMenuItem(MenuItem $menuItem): self
    {
        if ($this->menuItems->contains($menuItem)) {
            $this->menuItems->removeElement($menuItem);
            // set the owning side to null (unless already changed)
            if ($menuItem->getMenu() === $this) {
                $menuItem->setMenu(null);
            }
        }

        return $this;
    }

    public function getMenuArea(): ?MenuArea
    {
        return $this->menuArea;
    }

    public function setMenuArea(?MenuArea $menuArea): self
    {
        if($menuArea === null) {
            if($this->getMenuArea()->getMenu() == $this) {
                $this->getMenuArea()->setMenu(null);
            }
        }
        $this->menuArea = $menuArea;

        // set (or unset) the owning side of the relation if necessary
        $newMenu = null === $menuArea ? null : $this;
        if($menuArea !== null) {
            if ($menuArea->getMenu() !== $newMenu) {
                $menuArea->setMenu($newMenu);
            }
        }

        return $this;
    }
}
