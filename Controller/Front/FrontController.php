<?php

namespace Akyos\CoreBundle\Controller\Front;

use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\BuilderBundle\Entity\Component;
use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Akyos\CoreBundle\Services\CoreService;
use Akyos\CoreBundle\Services\FrontControllerService;
use Gedmo\Translatable\Entity\Translation;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class FrontController extends AbstractController
{
	protected KernelInterface $kernel;
	private CoreService $coreService;

	public function __construct(KernelInterface $kernel, CoreService $coreService)
	{
		$this->kernel = $kernel;
		$this->coreService = $coreService;
	}

	/**
	 * @Route("/", name="home", methods={"GET","POST"})
	 * @param CoreOptionsRepository $coreOptionsRepository
	 * @param PageRepository $pageRepository
	 * @param SeoRepository $seoRepository
	 * @param Environment $environment
	 * @return Response
	 */
	public function home(
		CoreOptionsRepository $coreOptionsRepository,
		PageRepository $pageRepository,
		SeoRepository $seoRepository,
		Environment $environment): Response
	{
		// FIND HOMEPAGE
		$entity = Page::class;
		$coreOptions = $coreOptionsRepository->findAll();
		$homePage = $coreOptions ? $coreOptions[0]->getHomepage() : $pageRepository->findOneBy([], ['position' => "ASC"]);

		if (!$homePage) {
			throw $this->createNotFoundException("Cette page n'existe pas! ( Accueil )");
		}

		// GET COMPONENTS OR CONTENT
		$components = null;
		if ($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
			$components = $this->getDoctrine()->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $homePage->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
		}

		// GET TEMPLATE
		$view = $homePage->getTemplate() ? 'home/' . $homePage->getTemplate() . '.html.twig' : '@AkyosCore/front/content.html.twig';

		$environment->addGlobal('global_page', $homePage);

		// RENDER
		return $this->render($view, [
			'seo' => $seoRepository->findOneBy(['type' => $entity, 'typeId' => $homePage->getId()]),
			'page' => $homePage,
			'components' => $components,
			'content' => $homePage->getContent(),
			'slug' => 'accueil'
		]);
	}

    /**
     * @Route("/page_preview/{slug}", name="page_preview", methods={"GET","POST"})
     * @param string $slug
     * @param FrontControllerService $frontControllerService
     * @return Response
     */
	public function pagePreview(string $slug, FrontControllerService $frontControllerService): Response
	{
		return new Response($frontControllerService->pageAndPreview($slug, 'page_preview'));
	}

    /**
     * @Route("/{slug}",
     *     methods={"GET","POST"},
     *     requirements={
     *          "slug"="^(?!admin\/|app\/|recaptcha\/|page_preview\/|archive\/|details\/|details_preview\/|categorie\/|tag\/|file-manager\/|secured_files\/|en\/).+"
     *     },
     *     name="page")
     * @param string $slug
     * @param FrontControllerService $frontControllerService
     * @return Response
     */
	public function page(string $slug, FrontControllerService $frontControllerService): Response
	{
		return new Response($frontControllerService->pageAndPreview($slug, 'page'));
	}

    /**
     * @Route("/archive/{entitySlug}", name="archive", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param string $entitySlug
     * @param CoreService $coreService
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
	public function archive(Filesystem $filesystem, string $entitySlug, CoreService $coreService, Request $request, PaginatorInterface $paginator): Response
	{
		// GET ENTITY NAME AND FULLNAME FROM SLUG
		[$entityFullName, $entity] = $coreService->getEntityAndFullString($entitySlug);

		if (!$entityFullName || !$entity) {
			throw $this->createNotFoundException("Cette page n'existe pas! ( Archive )");
		}
        if (!$this->coreService->checkIfArchiveEnable($entityFullName)) {
            throw $this->createNotFoundException('La page archive n\'est pas activée pour cette entité ');
        }

        // GET ELEMENTS
		// Pour avoir la fonction de recherche, ajouter dans le repository de l'entité visée la méthode "search"
		if (method_exists($this->getDoctrine()->getRepository($entityFullName), 'search')) {
			$elements = $paginator->paginate(
				$this->getDoctrine()->getRepository($entityFullName)->search($request->query->get('search')),
				$request->query->getInt('page', 1),
				10
			);
		} else {
			$param = [];
			$order = [];

			if (property_exists($entityFullName, 'published')) {
				$param['published'] = true;
			}
			if (property_exists($entityFullName, 'publishedAt')) {
				$order['publishedAt'] = 'ASC';
			}

			$elements = $paginator->paginate(
				$this->getDoctrine()->getRepository($entityFullName)->findBy($param, $order),
				$request->query->getInt('page', 1),
				10
			);
		}

		if (!$elements) {
			throw $this->createNotFoundException('Aucun élément pour cette entité! ');
		}

		// GET TEMPLATE
		$view = $filesystem->exists($this->kernel->getProjectDir() . '/templates/' . $entity . '/archive.html.twig')
			? "/${entity}/archive.html.twig"
			: '@AkyosCore/front/archive.html.twig';

		// RENDER
		return $this->render($view, [
			'elements' => $elements,
			'entity' => $entity,
			'slug' => $entitySlug
		]);
	}

    /**
     * @Route("/details_preview/{entitySlug}/{slug}", name="single_preview", methods={"GET","POST"})
     * @param string $entitySlug
     * @param string $slug
     * @param FrontControllerService $frontControllerService
     * @return Response
     */
	public function singlePreview(string $entitySlug, string $slug, FrontControllerService $frontControllerService): Response
	{
		return new Response($frontControllerService->singleAndPreview($entitySlug, $slug, 'single_preview'));
	}

    /**
     * @Route("/details/{entitySlug}/{slug}", name="single", methods={"GET","POST"})
     * @param string $entitySlug
     * @param string $slug
     * @param FrontControllerService $frontControllerService
     * @return Response
     */
	public function single(string $entitySlug, string $slug, FrontControllerService $frontControllerService): Response
	{
		return new Response($frontControllerService->singleAndPreview($entitySlug, $slug, 'single'));
	}

    /**
     * @Route("/categorie/{entitySlug}/{category}", name="taxonomy", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param string $entitySlug
     * @param string $category
     * @param CoreService $coreService
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
	public function category(Filesystem $filesystem, string $entitySlug, string $category, CoreService $coreService, Request $request, PaginatorInterface $paginator): Response
	{
		// GET ENTITY NAME AND FULLNAME FROM SLUG
		$meta = $this->getDoctrine()->getManager()->getMetadataFactory()->getAllMetadata();
		[$entityFullName, $entity] = $coreService->getEntityAndFullString($entitySlug);

		if (!$entityFullName || !$entity) {
			throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
		}

		// GET CATEGORY FULLNAME FROM ENTITY SLUG
		$categoryFullName = null;
		foreach ($meta as $m) {
			if (preg_match('/\x5c' . $entity . 'Category$/i', $m->getName())) {
				$categoryFullName = $m->getName();
			}
		}

		if (!$categoryFullName) {
			throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
		}

		// FIND ELEMENTS FROM CATEGORY OBJECT
		$categoryObject = $this->getDoctrine()->getRepository($categoryFullName)->findOneBy(['slug' => $category]) ??
			(!$this->getDoctrine()->getManager()->getMetadataFactory()->isTransient(Translation::class)
				? $this->getDoctrine()->getRepository(Translation::class)->findObjectByTranslatedField('slug', $category, $categoryFullName)
				: null);
		if (!$categoryObject) {
			throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
		}

		// GET ELEMENTS
		// Pour avoir la fonction de recherche, ajouter dans le repository de l'entité visée la méthode "searchByCategory"
		if (method_exists($this->getDoctrine()->getRepository($entityFullName), 'searchByCategory')) {
			$elements = $paginator->paginate(
				$this->getDoctrine()->getRepository($entityFullName)->searchByCategory($categoryObject, $request->query->get('search')),
				$request->query->getInt('page', 1),
				10
			);
		} else {
			$qb = $this->getDoctrine()->getRepository($entityFullName)->createQueryBuilder('a');
			$params = [];

			if (property_exists($entityFullName, 'postCategories')) {
				$qb
					->innerJoin('a.postCategories', 'apc')
					->andWhere($qb->expr()->eq('apc', ':cat'));
				$params['cat'] = $categoryObject;
			}
			if (property_exists($entityFullName, 'published')) {
				$qb->andWhere($qb->expr()->eq('a.published', true));
			}
			if (property_exists($entityFullName, 'publishedAt')) {
				$qb->orderBy('a.publishedAt', 'ASC');
			}

			$elements = $paginator->paginate(
				$qb->setParameters($params)->getQuery(),
				$request->query->getInt('page', 1),
				10
			);
		}

		// GET TEMPLATE
		$view = $filesystem->exists($this->kernel->getProjectDir() . '/templates/' . $entity . '/category.html.twig')
			? "${entity}/category.html.twig"
			: '@AkyosCore/front/category.html.twig';

		// RENDER
		return $this->render($view, [
			'elements' => $elements,
			'entity' => $entity,
			'slug' => $category,
			'category' => $categoryObject
		]);
	}

    /**
     * @Route("/tag/{entitySlug}/{tag}", name="tag", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param string $entitySlug
     * @param string $tag
     * @param CoreService $coreService
     * @return Response
     */
	public function tag(Filesystem $filesystem, string$entitySlug, string $tag, CoreService $coreService): Response
	{
		// GET ENTITY NAME AND FULLNAME FROM SLUG
		$meta = $this->getDoctrine()->getManager()->getMetadataFactory()->getAllMetadata();
		[$entityFullName, $entity] = $coreService->getEntityAndFullString($entitySlug);

		if (!$entityFullName || !$entity) {
			throw $this->createNotFoundException("Cette page n'existe pas! ( Étiquette )");
		}

		$parentEntity = str_replace('Tag', '', $entity);

		// GET TAG FULLNAME FROM ENTITY SLUG
		$tagFullName = null;
		foreach ($meta as $m) {
			if (preg_match('/' . $entity . '$/i', $m->getName())) {
				$tagFullName = $m->getName();
			}
		}

		if (!$tagFullName) {
			throw $this->createNotFoundException("Cette page n'existe pas! ( Étiquette )");
		}

		// FIND ELEMENTS FROM TAG OBJECT
		$tagObject = $this->getDoctrine()->getRepository($tagFullName)->findOneBy(['slug' => $tag]) ??
			(!$this->getDoctrine()->getManager()->getMetadataFactory()->isTransient(Translation::class)
				? $this->getDoctrine()->getRepository(Translation::class)->findObjectByTranslatedField('slug', $tag, $tagFullName)
				: null);
		if (!$tagObject) {
			throw $this->createNotFoundException("Cette page n'existe pas! ( Étiquette )");
		}
		if (substr($entity, -1) === "y") {
			$getter = 'get' . ucfirst(substr($parentEntity,0,-1)) . 'ies';
		} else {
			$getter = 'get' . ucfirst($parentEntity) . 's';
		}
		$elements = $tagObject->$getter();

		// GET TEMPLATE
		$view = $filesystem->exists($this->kernel->getProjectDir() . '/templates/' . $parentEntity . '/tag.html.twig')
			? "${parentEntity}/tag.html.twig"
			: '@AkyosCore/front/tag.html.twig';

		// RENDER
		return $this->render($view, [
			'elements' => $elements,
			'entity' => $entity,
			'slug' => $tag,
			'tag' => $tagObject
		]);
	}
}
