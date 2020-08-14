<?php

namespace Akyos\CoreBundle\Controller\Front;

use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\BuilderBundle\Entity\Component;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Akyos\CoreBundle\Services\CoreService;
use Akyos\CoreBundle\Services\FrontControllerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class FrontController extends AbstractController
{
    protected $kernel;
    /** @var CoreService */
    private $coreService;

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
        $entity = 'Page';
        $coreOptions = $coreOptionsRepository->findAll();
        $homePage = $coreOptions ? $coreOptions[0]->getHomepage() : $pageRepository->findOneBy([], ['position' => "ASC"]);

        if(!$homePage) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Accueil )");
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $this->getDoctrine()->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $homePage->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $homePage->getTemplate() ? '/home/'.$homePage->getTemplate().'.html.twig' : '@AkyosCore/front/content.html.twig';

        $environment->addGlobal('global_page', $homePage);

        // RENDER
        return $this->render($view, [
            'seo' => $seoRepository->findOneBy(array('type' => $entity, 'typeId' => $homePage->getId())),
            'page' => $homePage,
            'components' => $components,
            'content' => $homePage->getContent(),
            'slug' => 'accueil'
        ]);
    }

    /**
     * @Route("/page_preview/{slug}", name="page_preview", methods={"GET","POST"})
     * @param $slug
     * @param FrontControllerService $frontControllerService
     *
     * @return Response
     */
    public function pagePreview(
        $slug,
        FrontControllerService $frontControllerService): Response
    {
        return new Response($frontControllerService->pageAndPreview($slug, 'page_preview'));
    }

    /**
     * @Route("/{slug}",
     *     methods={"GET","POST"},
     *     requirements={
     *          "slug"="^(?!admin\/|app\/|recaptcha\/|page_preview\/|archive\/|details\/|details_preview\/|categorie\/|file-manager\/).+"
     *     },
     *     name="page")
     * @param $slug
     * @param FrontControllerService $frontControllerService
     *
     * @return Response
     */
    public function page(
        $slug,
        FrontControllerService $frontControllerService): Response
    {
        return new Response($frontControllerService->pageAndPreview($slug, 'page'));
    }

    /**
     * @Route("/archive/{entitySlug}", name="archive", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @param CoreService $coreService
     *
     * @return Response
     */
    public function archive(
        Filesystem $filesystem,
        $entitySlug,
        CoreService $coreService): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        [$entityFullName, $entity] = $coreService->getEntityAndFullString($entitySlug);

        if(!$entityFullName || !$entity) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Archive )");
        } else if(!$this->coreService->checkIfArchiveEnable($entity)) {
            throw $this->createNotFoundException('La page archive n\'est pas activée pour cette entité ');
        }

        // GET ELEMENTS
        $elements = $this->getDoctrine()->getRepository($entityFullName)->findAll();
        if(!$elements) {
            throw $this->createNotFoundException('Aucun élément pour cette entité! ');
        }

        // GET TEMPLATE
        $view = $filesystem->exists($this->kernel->getProjectDir().'/templates/'.$entity.'/archive.html.twig')
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
     * @param $slug
     *
     * @param FrontControllerService $frontControllerService
     *
     * @return Response
     */
    public function singlePreview(
        string $entitySlug,
        $slug,
        FrontControllerService $frontControllerService): Response
    {
        return new Response($frontControllerService->singleAndPreview($entitySlug, $slug, 'single_preview'));
    }

    /**
     * @Route("/details/{entitySlug}/{slug}", name="single", methods={"GET","POST"})
     * @param $entitySlug
     * @param $slug
     *
     * @param FrontControllerService $frontControllerService
     *
     * @return Response
     */
    public function single(
        $entitySlug,
        $slug,
        FrontControllerService $frontControllerService): Response
    {
        return new Response($frontControllerService->singleAndPreview($entitySlug, $slug, 'single'));
    }

    /**
     * @Route("/categorie/{entitySlug}/{category}", name="taxonomy", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @param $category
     * @param CoreService $coreService
     *
     * @return Response
     */
    public function category(
        Filesystem $filesystem,
        $entitySlug,
        $category,
        CoreService $coreService): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $meta = $this->getDoctrine()->getManager()->getMetadataFactory()->getAllMetadata();
        [$entityFullName, $entity] = $coreService->getEntityAndFullString($entitySlug);

        if(!$entityFullName || !$entity) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
        }

        // GET CATEGORY FULLNAME FROM ENTITY SLUG
        $categoryFullName = null;
        foreach ($meta as $m) {
            if(preg_match('/'.$entity.'Category$/i', $m->getName())) {
                $categoryFullName = $m->getName();
            }
        }

        if(!$categoryFullName) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
        }

        // FIND ELEMENTS FROM CATEGORY OBJECT
        $categoryObject = $this->getDoctrine()->getRepository($categoryFullName)->findOneBy(['slug' => $category]);
        if(!$categoryObject) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Catégorie )");
        }
        if(substr($entity, -1) === "y") {
            $getter = 'get'.substr(ucfirst($entity), 0,strlen($entity) - 1).'ies';
        } else {
            $getter = 'get'.ucfirst($entity).'s';
        }
        $elements = $categoryObject->$getter();

        // GET TEMPLATE
        $view = $filesystem->exists($this->kernel->getProjectDir().'/templates/'.$entity.'/category.html.twig')
            ? "/${entity}/category.html.twig"
            : '@AkyosCore/front/category.html.twig';

        // RENDER
        return $this->render($view, [
            'elements' => $elements,
            'entity' => $entity,
            'slug' => $category,
            'category' => $categoryObject
        ]);
    }
}