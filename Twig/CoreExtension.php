<?php

namespace Akyos\CoreBundle\Twig;

use Akyos\CoreBundle\Controller\CoreBundleController;
use Akyos\CoreBundle\Entity\Option;
use Akyos\CoreBundle\Entity\OptionCategory;
use Akyos\CoreBundle\Repository\OptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CoreExtension extends AbstractExtension
{
    private $corebundleController;
    private $em;
    private $router;

    public function __construct(CoreBundleController $coreBundleController, EntityManagerInterface $entityManager, UrlGeneratorInterface $router)
    {
        $this->corebundleController = $coreBundleController;
        $this->em = $entityManager;
        $this->router = $router;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('dynamicVariable', [$this, 'dynamicVariable']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dynamicVariable', [$this, 'dynamicVariable']),
            new TwigFunction('hasSeo', [$this, 'hasSeo']),
            new TwigFunction('getEntitySlug', [$this, 'getEntitySlug']),
            new TwigFunction('getMenu', [$this, 'getMenu']),
            new TwigFunction('instanceOf', [$this, 'isInstanceOf']),
            new TwigFunction('getOption', [$this, 'getOption']),
            new TwigFunction('getOptions', [$this, 'getOptions']),
            new TwigFunction('getElementSlug', [$this, 'getElementSlug']),
            new TwigFunction('getElement', [$this, 'getElement']),
            new TwigFunction('getPermalink', [$this, 'getPermalink']),
            new TwigFunction('checkChildActive', [$this, 'checkChildActive']),
        ];
    }

    public function dynamicVariable($el, $field)
    {
        $getter = 'get'.$field;
        $value = $el->$getter();
        if(is_array($value)) {
            $arrayValue = "";
            foreach ($value as $key => $item) {
                $arrayValue .= $item;
                if($key != (count($value) - 1)) {
                    $arrayValue .= ", ";
                }
            }
            return $arrayValue;
        }
        return $value;
    }

    public function hasSeo($entity)
    {
        $hasSeo = $this->corebundleController->checkIfSeoEnable($entity)->getContent();
        if ($hasSeo === "true") {
            return true;
        } else {
            return false;
        }
    }

    public function getEntitySlug($entity)
    {
        if(preg_match('/Category/i', $entity)) {
            $entity = str_replace('Category', '', $entity);
        }

        $entityFullName = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[sizeof($entityName)-1];
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|Category/i', $entityName)) {
                if(preg_match('/^'.$entity.'$/i', $entityName)) {
                    $entityFullName = $m->getName();
                }
            }
        }
        if(!$entityFullName) {
            return $entity;
        }
        return $entityFullName::ENTITY_SLUG;
    }

    public function getMenu($menuSlug, $page)
    {
        $menu = $this->corebundleController->renderMenu($menuSlug, $page);
        return $menu;
    }

    public function isInstanceOf($object, $class) {
        if (!is_object($object)) {
            return false;
        }
        $reflectionClass = new \ReflectionClass($class);
        return $reflectionClass->isInstance($object);
    }

    public function getOption($optionSlug)
    {
        $option = $this->em->getRepository(Option::class)->findOneBy(array('slug' => $optionSlug));
        return $option;
    }
    public function getOptions($optionsSlug)
    {
        $result = null;
        $options = $this->em->getRepository(OptionCategory::class)->findOneBy(array('slug' => $optionsSlug));
        foreach ($options->getOptions() as $option) {
            if ($option instanceof Option) {
                $result[$option->getSlug()] = $option->getValue();
            }
        }

        return $result;
    }

    public function getElementSlug($type, $typeId)
    {
        if(preg_match('/Category/i', $type)) {
            $entity = str_replace('Category', '', $type);
        }

        $entityFullName = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[sizeof($entityName)-1];
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User/i', $entityName)) {
                if(preg_match('/^'.$type.'$/i', $entityName)) {
                    $entityFullName = $m->getName();
                }
            }
        }

        $slug = $this->em->getRepository($entityFullName)->find($typeId)->getSlug();

        return $slug;
    }

    public function getElement($type, $typeId)
    {
        if ($typeId == null) {
            return false;
        }

        if(preg_match('/Category/i', $type)) {
            $entity = str_replace('Category', '', $type);
        }

        $entityFullName = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[sizeof($entityName)-1];
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User/i', $entityName)) {
                if(preg_match('/^'.$type.'$/i', $entityName)) {
                    $entityFullName = $m->getName();
                }
            }
        }

        if($entityFullName) {
            $slug = $this->em->getRepository($entityFullName)->find($typeId);
        } else {
            $slug = "page_externe";
        }

        return $slug;
    }

    public function getPermalink($item)
    {
        $link = '';
        if($item->getUrl()) {
            $link = $item->getUrl();
        } elseif ($item->getType()) {
            if ( ($item->getType() == 'Page') && $item->getIdType() ) {
                $link = $this->router->generate('page', ['slug' => $this->getElementSlug($item->getType(), $item->getIdType())]);
            } elseif ( ($item->getType() != 'Page') && $item->getIdType() ) {
                $link = $this->router->generate('single', ['entitySlug' => $this->getEntitySlug($item->getType()), 'slug' => $this->getElementSlug($item->getType(), $item->getIdType())]);
            } elseif ( ($item->getType() != 'Page') && $item->getIsParent() ) {
                $link = $this->router->generate('archive', ['entitySlug' => $this->getEntitySlug($item->getType())]);
            } else {
                $link = null;
            }
        }

        return $link;
    }

    public function checkChildActive($item, $current)
    {
        foreach ($item->getMenuItemsChilds() as $child){
            if ($child) {
                if ($current == $this->getElement($child->getType(), $child->getIdType())) {
                    return true;
                }
            }
        }
        return false;
    }
}
