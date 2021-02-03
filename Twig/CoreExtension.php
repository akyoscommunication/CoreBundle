<?php

namespace Akyos\CoreBundle\Twig;

use Akyos\CoreBundle\Controller\Back\CoreBundleController;
use Akyos\CoreBundle\Entity\CustomFieldValue;
use Akyos\CoreBundle\Entity\Option;
use Akyos\CoreBundle\Entity\OptionCategory;
use Akyos\CoreBundle\Entity\Post;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\CustomFieldRepository;
use Akyos\CoreBundle\Repository\CustomFieldValueRepository;
use Akyos\CoreBundle\Services\CoreMailer;
use Akyos\CoreBundle\Services\CoreService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CoreExtension extends AbstractExtension
{
    private $corebundleController;
    private $em;
    private $router;
    private $coreOptionsRepository;
    private $coreService;
    /** @var ContainerInterface */
    private $container;
    private $mailer;
    private $twig;


    public function __construct(
        CoreBundleController $coreBundleController,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $router,
        CoreOptionsRepository $coreOptionsRepository,
        CoreService $coreService,
        ContainerInterface $container,
        CoreMailer $mailer,
        Environment $twig
    )
    {
        $this->corebundleController = $coreBundleController;
        $this->em = $entityManager;
        $this->router = $router;
        $this->coreOptionsRepository = $coreOptionsRepository;
        $this->coreService = $coreService;
        $this->container = $container;
        $this->mailer = $mailer;
        $this->twig = $twig;

    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('dynamicVariable', [$this, 'dynamicVariable']),
            new TwigFilter('truncate', [$this, 'truncate']),
            new TwigFilter('lcfirst', [$this, 'lcfirst']),

        ];
    }

    public function truncate($value, int $length, string $after)
    {
        return mb_substr($value, 0, $length, 'UTF-8').$after;
    }

    public function lcfirst($value)
    {
        return lcfirst($value);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dynamicVariable', [$this, 'dynamicVariable']),
            new TwigFunction('hasSeo', [$this, 'hasSeo']),
            new TwigFunction('getEntitySlug', [$this, 'getEntitySlug']),
            new TwigFunction('getEntityNameSpace', [$this, 'getEntityNameSpace']),
            new TwigFunction('matchSameEntity', [$this, 'matchSameEntity']),
            new TwigFunction('isArchive', [$this, 'isArchive']),
            new TwigFunction('getMenu', [$this, 'getMenu']),
            new TwigFunction('instanceOf', [$this, 'isInstanceOf']),
            new TwigFunction('getOption', [$this, 'getOption']),
            new TwigFunction('getOptions', [$this, 'getOptions']),
            new TwigFunction('getElementSlug', [$this, 'getElementSlug']),
            new TwigFunction('getElement', [$this, 'getElement']),
            new TwigFunction('getElementsList', [$this, 'getElementsList']),
            new TwigFunction('getCategoryList', [$this, 'getCategoryList']),
            new TwigFunction('getPermalink', [$this, 'getPermalink']),
            new TwigFunction('getPermalinkById', [$this, 'getPermalinkById']),
            new TwigFunction('checkChildActive', [$this, 'checkChildActive']),
            new TwigFunction('getBundleTab', [$this, 'getBundleTab']),
            new TwigFunction('getBundleTabContent', [$this, 'getBundleTabContent']),
            new TwigFunction('sendExceptionMail', [$this, 'sendExceptionMail']),
            new TwigFunction('get_class', 'get_class'),
            new TwigFunction('class_exists', 'class_exists'),
            new TwigFunction('getCustomField', [$this->coreService, 'getCustomField']),
            new TwigFunction('setCustomField', [$this->coreService, 'setCustomField']),
            new TwigFunction('searchByCustomField', [$this->coreService, 'searchByCustomField']),
            new TwigFunction('hasCategory', [$this, 'hasCategory']),
            new TwigFunction('countElements', [$this, 'countElements']),
        ];
    }

    public function dynamicVariable($el, $field)
    {
        $getter = 'get'.$field;
        if(count(explode(';', $field)) > 1) {
            $getter1 = 'get'.explode(';', $field)[0];
            $getter2 = 'get'.explode(';', $field)[1];
            $value = $el->$getter1() ? $el->$getter1()->$getter2() : '';
        } else {
            $value = $el->$getter();
        }
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

    public function hasSeo($entity): bool
    {
        return $this->coreService->checkIfSeoEnable($entity) ?: false;
    }

    public function getEntitySlug($entity)
    {
        return defined($entity.'::ENTITY_SLUG') ? $entity::ENTITY_SLUG : false;
    }

    public function getEntityNameSpace($entity)
    {
        $entityFullName = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[sizeof($entityName)-1];
            if(preg_match('/^'.$entity.'$/i', $entityName)) {
                $entityFullName = $m->getName();
            }
        }
        if(!$entityFullName) {
            return $entity;
        }
        return $entityFullName;
    }

    public function matchSameEntity($str, $entity) {
        if (!is_object($entity)) {
            return false;
        }
        return ($str == get_class($entity) ? true : false);
    }

    public function isArchive($entity, $page) {
        if (!is_array($page)) {
            return false;
        } elseif (!empty($page) && !is_object($page[0])) {
            return false;
        }
        return ( !empty($page) ? ($entity == get_class($page[0]) ? true : false) : false );
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
            if(preg_match('/^'.$type.'$/i', $entityName)) {
                $entityFullName = $m->getName();
            }
        }

        $el = $this->em->getRepository($entityFullName)->find($typeId);

        if (!$el) {
            return false;
        } else {
            $slug = $el->getSlug();

            return $slug;
        }
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
            if(preg_match('/^'.$type.'$/i', $entityName)) {
                $entityFullName = $m->getName();
            }
        }

        if($entityFullName) {
            $slug = $this->em->getRepository($entityFullName)->find($typeId);
        } else {
            $slug = "page_externe";
        }

        return $slug;
    }

    public function getElementsList($type)
    {
        if ($type == null) {
            return false;
        }

        if(preg_match('/Category/i', $type)) {
            $entity = str_replace('Category', '', $type);
        }

        $entityFullName = null;
        $entityFields = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[count($entityName)-1];
            if(preg_match('/^'.$type.'$/i', $entityName)) {
                $entityFullName = $m->getName();
                $entityFields = $m->getFieldNames();
            }
        }

        if($entityFullName) {
            if(in_array('position', $entityFields, true)) {
                $elements = $this->em->getRepository($entityFullName)->findBy([], ['position' => 'ASC']);
            } else {
                $elements = $this->em->getRepository($entityFullName)->findAll();
            }
        }

        return ($elements ?? null);
    }

    public function getCategoryList($type)
    {
        if ($type == null) {
            return false;
        }else{
            $type = $type.'Category';
        }

        $entityFullName = null;
        $entityFields = null;
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entityName = explode('\\', $m->getName());
            $entityName = $entityName[count($entityName)-1];
            if(preg_match('/^'.$type.'$/i', $entityName)) {
                $entityFullName = $m->getName();
                $entityFields = $m->getFieldNames();
            }
        }

        if($entityFullName) {
            if(in_array('position', $entityFields, true)) {
                $elements = $this->em->getRepository($entityFullName)->findBy([], ['position' => 'ASC']);
            } else {
                $elements = $this->em->getRepository($entityFullName)->findAll();
            }
        }

        return ($elements ?? null);
    }

    public function getPermalinkById($type, $id)
    {
        $link = '';
        if ( $type == 'Page' && $id ) {
            $coreOptions = $this->coreOptionsRepository->findAll();
            $homepage = $coreOptions[0]->getHomepage();
            $isHome = false;
            if($homepage) {
                if($homepage->getId() === $id) {
                    $isHome = true;
                }
            }
            if($isHome) {
                $link = $this->router->generate('home');
            } else {
                $link = $this->router->generate('page', ['slug' => $this->getElementSlug($type, $id)]);
            }
        } elseif ( ($type != 'Page') && $id ) {
            $link = $this->router->generate('single', ['entitySlug' => $this->getEntitySlug($type), 'slug' => $this->getElementSlug($type, $id)]);
        } elseif ( ($type != 'Page') &&  !$id) {
            $link = $this->router->generate('archive', ['entitySlug' => $this->getEntitySlug($type)]);
        } else {
            $link = null;
        }


        return $link;
    }

    public function getPermalink($item)
    {
        $urlPaterne = "/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:_\/?#[\]@!\$&'\(\)\*\+,;=.]+$/";
        $link = '';
        if($item->getUrl()) {
            if(preg_match($urlPaterne, $item->getUrl())){
                $link = $item->getUrl();
            }else{
                $link = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://".$_SERVER["HTTP_HOST"].$item->getUrl());
            }
        } elseif ($item->getType()) {
            if ( ($item->getType() == 'Page') && $item->getIdType() ) {
                $coreOptions = $this->coreOptionsRepository->findAll();
                $homepage = $coreOptions[0]->getHomepage();
                $isHome = false;
                if($homepage) {
                    if($homepage->getId() === $item->getIdType()) {
                        $isHome = true;
                    }
                }
                if($isHome) {
                    $link = $this->router->generate('home');
                } else {
                    $link = $this->router->generate('page', ['slug' => $this->getElementSlug($item->getType(), $item->getIdType())]);
                }
            } elseif ( ($item->getType() != 'Page') && $item->getIdType() ) {
                $slug = $this->getElementSlug($item->getType(), $item->getIdType());
                if ($slug) {
                    $link = $this->router->generate('single', ['entitySlug' => $this->getEntitySlug($item->getType()), 'slug' => $slug]);
                }
            } elseif ( ($item->getType() != 'Page') &&  !$item->getIdType()) {
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

    public function getBundleTab($objectType)
    {
        $html = '';
        $class = 'Akyos\BuilderBundle\Service\Builder';
        if (class_exists($class)) {
            if($this->coreService->checkIfBundleEnable('Akyos\BuilderBundle\AkyosBuilderBundle', 'Akyos\BuilderBundle\Entity\BuilderOptions', $objectType)) {
                $html .= $this->container->get('render.builder')->getTab();
            }
        }

        return $html;
    }

    public function getBundleTabContent($objectType, $objectId)
    {
        $html = '';
        $class = 'Akyos\BuilderBundle\Service\Builder';
        if (class_exists($class)) {
            if($this->coreService->checkIfBundleEnable('Akyos\BuilderBundle\AkyosBuilderBundle', 'Akyos\BuilderBundle\Entity\BuilderOptions', $objectType)) {
                $html .= $this->container->get('render.builder')->getTabContent($objectType, $objectId);
            }
        }

        return $html;
    }

    public function sendExceptionMail($exceptionMessage)
    {
        try {
			$this->mailer->sendMail(
				["thomas.sebert.akyos@gmail.com"],
				'Nouvelle erreur sur le site '.$_SERVER['SERVER_NAME'],
				$exceptionMessage,
				'Nouvelle erreur sur le site '.$_SERVER['SERVER_NAME'],
				null,
				null,
				["lilian.akyos@gmail.com", "johan@akyos.com"]
			);
            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }



    public function hasCategory(string $slug, Post $post): bool
    {
        $hasCategory = false;
        foreach($post->getPostCategories() as $postCategory) {
            if($postCategory->getSlug() === $slug) {
                $hasCategory = true;
            }
        }
        return $hasCategory;
    }
	
	public function countElements(string $entity): int
	{
		return $this->em->getRepository($entity)->count([]);
	}
}
