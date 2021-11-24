<?php

namespace Akyos\CoreBundle\Twig;

use Akyos\CoreBundle\Controller\Back\CoreBundleController;
use Akyos\CoreBundle\Entity\Option;
use Akyos\CoreBundle\Entity\OptionCategory;
use Akyos\CoreBundle\Entity\Post;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Services\CoreMailer;
use Akyos\CoreBundle\Services\CoreService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CoreExtension extends AbstractExtension
{
	private CoreBundleController $corebundleController;
	private EntityManagerInterface $em;
	private UrlGeneratorInterface $router;
	private CoreOptionsRepository $coreOptionsRepository;
	private CoreService $coreService;
	private ContainerInterface $container;
	private CoreMailer $mailer;
	
	
	public function __construct(
		CoreBundleController $coreBundleController,
		EntityManagerInterface $entityManager,
		UrlGeneratorInterface $router,
		CoreOptionsRepository $coreOptionsRepository,
		CoreService $coreService,
		ContainerInterface $container,
		CoreMailer $mailer
	)
	{
		$this->corebundleController = $coreBundleController;
		$this->em = $entityManager;
		$this->router = $router;
		$this->coreOptionsRepository = $coreOptionsRepository;
		$this->coreService = $coreService;
		$this->container = $container;
		$this->mailer = $mailer;
		
	}

    /**
     * @return TwigFilter[]
     */
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

    /**
     * @param $value
     * @param int $length
     * @param string $after
     * @return string
     */
    public function truncate($value, int $length, string $after)
    {
        if(strlen($value) < $length) {
            return mb_substr($value, 0, $length, 'UTF-8') . $after;
        }
        return $value;
    }

    /**
     * @param $value
     * @return string
     */
	public function lcfirst($value): string
    {
		return lcfirst($value);
	}

    /**
     * @return TwigFunction[]
     */
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
			new TwigFunction('useClosure', [$this, 'useClosure']),
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

    /**
     * @param $el
     * @param $field
     * @return string
     */
	public function dynamicVariable($el, $field): string
    {
		$getter = 'get' . $field;
		if (count(explode(';', $field)) > 1) {
			$getter1 = 'get' . explode(';', $field)[0];
			$getter2 = 'get' . explode(';', $field)[1];
			$value = $el->$getter1() ? $el->$getter1()->$getter2() : '';
		} else {
			$value = $el->$getter();
		}
		if (is_array($value)) {
			$arrayValue = "";
			foreach ($value as $key => $item) {
				$arrayValue .= $item;
				if ($key !== (count($value) - 1)) {
					$arrayValue .= ", ";
				}
			}
			return $arrayValue;
		}
		return $value;
	}

    /**
     * @param $entity
     * @return bool
     */
	public function hasSeo($entity): bool
	{
		return $this->coreService->checkIfSeoEnable($entity) ?: false;
	}

    /**
     * @param $entity
     * @return false
     */
	public function getEntitySlug($entity): bool
    {
		if (!class_exists($entity)) {
			$entity = $this->getEntityNameSpace($entity);
		}
		return defined($entity . '::ENTITY_SLUG') ? $entity::ENTITY_SLUG : false;
	}

    /**
     * @param $entity
     * @return string
     */
	public function getEntityNameSpace($entity): string
    {
		$entityFullName = null;
		$meta = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			$entityName = explode('\\', $m->getName());
			$entityName = $entityName[count($entityName) - 1];
			if (preg_match('/^' . $entity . '$/i', $entityName)) {
				$entityFullName = $m->getName();
			}
		}
		if (!$entityFullName) {
			return $entity;
		}
		return $entityFullName;
	}

    /**
     * @param $str
     * @param $entity
     * @return bool
     */
	public function matchSameEntity($str, $entity): bool
    {
		if (!is_object($entity)) {
			return false;
		}
		return $str === get_class($entity);
	}

    /**
     * @param $entity
     * @param $page
     * @return bool
     */
	public function isArchive($entity, $page): bool
    {
        if (!is_array($page)) {
            return false;
        }

        if (!empty($page) && !is_object($page[0])) {
            return false;
        }
        return (!empty($page) ? ($entity === get_class($page[0])) : false);
	}

    /**
     * @param $menuSlug
     * @param $page
     * @return string
     */
	public function getMenu($menuSlug, $page): string
    {
        return $this->corebundleController->renderMenu($menuSlug, $page);
	}

    /**
     * @param $object
     * @param null $class
     * @return bool|string
     * @throws ReflectionException
     */
	public function isInstanceOf($object, $class = null)
	{
		if(!$class) {
			return gettype($object);
		}
		if (!is_object($object)) {
			return false;
		}
		$reflectionClass = new \ReflectionClass($class);
		return $reflectionClass->isInstance($object);
	}

    /**
     * @param \Closure $closure
     * @param $params
     * @return mixed
     */
	public function useClosure(\Closure $closure, $params)
	{
		return $closure($params);
	}

    /**
     * @param $optionSlug
     * @return object|null
     */
	public function getOption($optionSlug): ?object
    {
        return $this->em->getRepository(Option::class)->findOneBy(['slug' => $optionSlug]);
	}

    /**
     * @param $optionsSlug
     * @return array
     * @throws JsonException
     */
	public function getOptions($optionsSlug): array
    {
		$result = null;
		/** @var OptionCategory $options */
		$options = $this->em->getRepository(OptionCategory::class)->findOneBy(['slug' => $optionsSlug]);
		/** @var Option $option */
		foreach ($options->getOptions() as $option) {
			if ($option instanceof Option) {
				$result[$option->getSlug()] = $option->getValue();
			}
		}
		
		return $result;
	}

    /**
     * @param $type
     * @param $typeId
     * @return false
     */
	public function getElementSlug($type, $typeId): bool
    {
		if (false !== stripos($type, "Category")) {
			$entity = str_replace('Category', '', $type);
		}
		
		$entityFullName = null;
		$meta = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			$entityName = explode('\\', $m->getName());
			$entityName = $entityName[count($entityName) - 1];
			if (preg_match('/^' . $type . '$/i', $entityName)) {
				$entityFullName = $m->getName();
			}
		}
		
		$el = $this->em->getRepository($entityFullName)->find($typeId);
		
		if (!$el) {
			return false;
		}

        return $el->getSlug();
    }

    /**
     * @param $type
     * @param $typeId
     * @return false|object|string
     */
	public function getElement($type, $typeId)
	{
		if (!$typeId) {
			return false;
		}
		
		if (false !== stripos($type, "Category")) {
			str_replace('Category', '', $type);
		}
		
		$entityFullName = null;
		$meta = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			$entityName = explode('\\', $m->getName());
			$entityName = $entityName[count($entityName) - 1];
			if (preg_match('/^' . $type . '$/i', $entityName)) {
				$entityFullName = $m->getName();
			}
		}
		
		if ($entityFullName) {
			$slug = $this->em->getRepository($entityFullName)->find($typeId);
		} else {
			$slug = "page_externe";
		}
		
		return $slug;
	}

    /**
     * @param $type
     * @return false|object[]|null
     */
	public function getElementsList($type)
	{
		if (!$type) {
			return false;
		}
		
		if (false !== stripos($type, "Category")) {
			str_replace('Category', '', $type);
		}
		
		$entityFullName = null;
		$entityFields = null;
		$meta = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			$entityName = explode('\\', $m->getName());
			$entityName = $entityName[count($entityName) - 1];
			if (preg_match('/^' . $type . '$/i', $entityName)) {
				$entityFullName = $m->getName();
				$entityFields = $m->getFieldNames();
			}
		}
		
		if ($entityFullName) {
			if (in_array('position', $entityFields, true)) {
				$elements = $this->em->getRepository($entityFullName)->findBy([], ['position' => 'ASC']);
			} else {
				$elements = $this->em->getRepository($entityFullName)->findAll();
			}
		}
		
		return ($elements ?? null);
	}

    /**
     * @param $type
     * @return false|object[]|null
     */
	public function getCategoryList($type)
	{
		if (!$type) {
			return false;
		}

        $type .= 'Category';

        $entityFullName = null;
		$entityFields = null;
		$meta = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			$entityName = explode('\\', $m->getName());
			$entityName = $entityName[count($entityName) - 1];
			if (preg_match('/^' . $type . '$/i', $entityName)) {
				$entityFullName = $m->getName();
				$entityFields = $m->getFieldNames();
			}
		}
		
		if ($entityFullName) {
			if (in_array('position', $entityFields, true)) {
				$elements = $this->em->getRepository($entityFullName)->findBy([], ['position' => 'ASC']);
			} else {
				$elements = $this->em->getRepository($entityFullName)->findAll();
			}
		}
		
		return ($elements ?? null);
	}

    /**
     * @param $type
     * @param $id
     * @return string|null
     */
	public function getPermalinkById($type, $id): ?string
    {
		$link = '';
		if ($type === 'Page' && $id) {
			$coreOptions = $this->coreOptionsRepository->findAll();
			$homepage = $coreOptions[0]->getHomepage();
			$isHome = false;
			if ($homepage && $homepage->getId() === $id) {
                $isHome = true;
            }
			if ($isHome) {
				$link = $this->router->generate('home');
			} else {
				$link = $this->router->generate('page', ['slug' => $this->getElementSlug($type, $id)]);
			}
		} elseif (($type !== 'Page') && $id) {
			$link = $this->router->generate('single', ['entitySlug' => $this->getEntitySlug($type), 'slug' => $this->getElementSlug($type, $id)]);
		} elseif (($type !== 'Page') && !$id) {
			$link = $this->router->generate('archive', ['entitySlug' => $this->getEntitySlug($type)]);
		} else {
			$link = null;
		}

		return $link;
	}

    /**
     * @param $item
     * @return string|null
     */
	public function getPermalink($item): ?string
    {
		$urlPaterne = "/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:_\/?#[\]@!\$&'\(\)\*\+,;=.]+$/";
		$link = '';
		if ($item->getUrl()) {
			if (preg_match($urlPaterne, $item->getUrl())) {
				$link = $item->getUrl();
			} else {
				$link = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $item->getUrl());
			}
		} elseif ($item->getType()) {
			if (($item->getType() === 'Page') && $item->getIdType()) {
				$coreOptions = $this->coreOptionsRepository->findAll();
				$homepage = $coreOptions[0]->getHomepage();
				$isHome = false;
				if ($homepage && $homepage->getId() === $item->getIdType()) {
                    $isHome = true;
                }
				if ($isHome) {
					$link = $this->router->generate('home');
				} else {
					$link = $this->router->generate('page', ['slug' => $this->getElementSlug($item->getType(), $item->getIdType())]);
				}
			} elseif (($item->getType() !== 'Page') && $item->getIdType()) {
				$slug = $this->getElementSlug($item->getType(), $item->getIdType());
				if ($slug) {
					$link = $this->router->generate('single', ['entitySlug' => $this->getEntitySlug($item->getType()), 'slug' => $slug]);
				}
			} elseif (($item->getType() !== 'Page') && !$item->getIdType()) {
				$link = $this->router->generate('archive', ['entitySlug' => $this->getEntitySlug($item->getType())]);
			} else {
				$link = null;
			}
		}
		
		return $link;
	}

    /**
     * @param $item
     * @param $current
     * @return bool
     */
	public function checkChildActive($item, $current): bool
    {
		foreach ($item->getMenuItemsChilds() as $child) {
			if ($child && $current === $this->getElement($child->getType(), $child->getIdType())) {
                return true;
            }
		}
		return false;
	}

    /**
     * @param $objectType
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
	public function getBundleTab($objectType): string
    {
		$html = '';
		$class = 'Akyos\BuilderBundle\Service\Builder';
		if (class_exists($class) && $this->coreService->checkIfBundleEnable('Akyos\BuilderBundle\AkyosBuilderBundle', 'Akyos\BuilderBundle\Entity\BuilderOptions', $objectType)) {
            $html .= $this->container->get('render.builder')->getTab();
        }
		
		return $html;
	}

    /**
     * @param $objectType
     * @param $objectId
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
	public function getBundleTabContent($objectType, $objectId): string
    {
		$html = '';
		$class = 'Akyos\BuilderBundle\Service\Builder';
		if (class_exists($class) && $this->coreService->checkIfBundleEnable('Akyos\BuilderBundle\AkyosBuilderBundle', 'Akyos\BuilderBundle\Entity\BuilderOptions', $objectType)) {
            $html .= $this->container->get('render.builder')->getTabContent($objectType, $objectId);
        }
		
		return $html;
	}

    /**
     * @param $exceptionMessage
     * @return bool|Exception
     */
	public function sendExceptionMail($exceptionMessage)
	{
		try {
			$this->mailer->sendMail(
				["thomas.sebert.akyos@gmail.com"],
				'Nouvelle erreur sur le site ' . $_SERVER['SERVER_NAME'],
				$exceptionMessage,
				'Nouvelle erreur sur le site ' . $_SERVER['SERVER_NAME'],
				null,
				null,
				["lilian.akyos@gmail.com", "johan@akyos.com"],
                null,
                null,
                null,
                null,
                'SMTP'
			);
			return true;
		} catch (Exception $e) {
			return $e;
		}
	}

    /**
     * @param string $slug
     * @param Post $post
     * @return bool
     */
	public function hasCategory(string $slug, Post $post): bool
	{
		$hasCategory = false;
		foreach ($post->getPostCategories() as $postCategory) {
			if ($postCategory->getSlug() === $slug) {
				$hasCategory = true;
			}
		}
		return $hasCategory;
	}

    /**
     * @param string $entity
     * @return int
     */
	public function countElements(string $entity): int
	{
		return $this->em->getRepository($entity)->count([]);
	}
}
