<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\BuilderBundle\Entity\Component;
use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\Redirect301Repository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Akyos\CoreBundle\Services\CoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @Route("/{slug}", name="page", methods={"GET","POST"}, requirements={"slug"="^(?!admin\/|app\/|recaptcha\/|archive\/|details\/|details_preview\/|categorie\/|file-manager\/).+"})
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @param Redirect301Repository $redirect301Repository
     * @param $slug
     * @param Environment $environment
     * @return Response
     */
    public function page(
        PageRepository $pageRepository,
        SeoRepository $seoRepository,
        Redirect301Repository $redirect301Repository,
        $slug,
        Environment $environment): Response
    {
        // FIND PAGE
        $entity = 'Page';
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if(!$page) {
            $redirect301 = $redirect301Repository->findOneBy(['oldSlug' => $slug, 'objectType' => Page::class]);
            if($redirect301) {
                $page = $pageRepository->find($redirect301->getObjectId());
                $redirectUrl = $this->generateUrl('page', ['slug' => $page->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
            throw $this->createNotFoundException("Cette page n'existe pas! ( ${entity} )");
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $this->getDoctrine()->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $page->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $page->getTemplate() ? '/page/'.$page->getTemplate().'.html.twig' : '@AkyosCore/front/content.html.twig';

        $environment->addGlobal('global_page', $page);

        if ($page->getPublished()) {
            return $this->render($view, [
                'seo' => $seoRepository->findOneBy(array('type' => $entity, 'typeId' => $page->getId())),
                'page' => $page,
                'components' => $components,
                'content' => $page->getContent(),
                'slug' => $slug
            ]);
        } else {
            throw $this->createNotFoundException("Cette page n'existe pas! ( ${entity} )");
        }
    }

    /**
     * @Route("page_preview/{slug}", name="page_preview", methods={"GET","POST"}, requirements={"slug"="^(?!admin\/|app\/|recaptcha\/|archive\/|details\/|details_preview\/|categorie\/|file-manager\/).+"})
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @param Redirect301Repository $redirect301Repository
     * @param $slug
     * @param Environment $environment
     * @return Response
     */
    public function pagePreview(
        PageRepository $pageRepository,
        SeoRepository $seoRepository,
        Redirect301Repository $redirect301Repository,
        $slug,
        Environment $environment): Response
    {
        // FIND PAGE
        $entity = 'Page';
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if(!$page) {
            $redirect301 = $redirect301Repository->findOneBy(['oldSlug' => $slug, 'objectType' => Page::class]);
            if($redirect301) {
                $page = $pageRepository->find($redirect301->getObjectId());
                $redirectUrl = $this->generateUrl('page_preview', ['slug' => $page->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
            throw $this->createNotFoundException("Cette page n'existe pas! ( Page Preview )");
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $this->getDoctrine()->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $page->getId(), 'isTemp' => true, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $page->getTemplate() ? '/page/'.$page->getTemplate().'.html.twig' : '@AkyosCore/front/content.html.twig';
        $environment->addGlobal('global_page', $page);

        return $this->render($view, [
            'seo' => $seoRepository->findOneBy(array('type' => 'Page', 'typeId' => $page->getId())),
            'page' => $page,
            'components' => $components,
            'slug' => $slug
        ]);
    }

    /**
     * @Route("/archive/{entitySlug}", name="archive", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @return Response
     */
    public function archive(
        Filesystem $filesystem,
        $entitySlug): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $entityFullName = null;
        $entity = null;
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                try {
                    $constant_reflex = new \ReflectionClassConstant($m->getName(), 'ENTITY_SLUG');
                    $constant_value = $constant_reflex->getValue();
                } catch (\ReflectionException $e) {
                    $constant_value = null;
                }
                if(null !== $constant_value) {
                    if($m->getName()::ENTITY_SLUG === $entitySlug) {
                        $entityFullName = $m->getName();
                        $entity = array_reverse(explode('\\', $entityFullName))[0];
                    }
                }
            }
        }

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
     * @Route("/details/{entitySlug}/{slug}", name="single", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @param $slug
     * @param SeoRepository $seoRepository
     * @param Redirect301Repository $redirect301Repository
     * @param Environment $environment
     *
     * @return Response
     */
    public function single(
        Filesystem $filesystem,
        $entitySlug,
        $slug,
        SeoRepository $seoRepository,
        Redirect301Repository $redirect301Repository,
        Environment $environment): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $entityFullName = null;
        $entity = null;
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                try {
                    $constant_reflex = new \ReflectionClassConstant($m->getName(), 'ENTITY_SLUG');
                    $constant_value = $constant_reflex->getValue();
                } catch (\ReflectionException $e) {
                    $constant_value = null;
                }
                if(null !== $constant_value) {
                    if($m->getName()::ENTITY_SLUG === $entitySlug) {
                        $entityFullName = $m->getName();
                        $entity = array_reverse(explode('\\', $entityFullName))[0];
                    }
                }
            }
        }

        if(!$entityFullName || !$entity || !$this->coreService->checkIfSingleEnable($entity)) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail )");
        }

        // GET ELEMENT
        $element = $this->getDoctrine()->getRepository($entityFullName)->findOneBy(['slug' => $slug]);
        if(!$element) {
            $redirect301 = $redirect301Repository->findOneBy(['oldSlug' => $slug, 'objectType' => $entityFullName]);
            if($redirect301) {
                $element = $this->getDoctrine()->getRepository($entityFullName)->find($redirect301->getObjectId());
                $redirectUrl = $this->generateUrl('single', ['entitySlug' => $entitySlug, 'slug' => $element->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail )");
        } else if (property_exists($element, 'published') and !$element->getPublished()) {
            return $this->redirectToRoute('single_preview', ['entitySlug' => $entitySlug, 'slug' => $slug]);
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $this->getDoctrine()->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $element->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $filesystem->exists($this->kernel->getProjectDir()."/templates/${entity}/single.html.twig")
            ? "/${entity}/single.html.twig"
            : '@AkyosCore/front/single.html.twig';
        $environment->addGlobal('global_element', $element);

        // RENDER
        return $this->render($view, [
            'seo' => $seoRepository->findOneBy(array('type' => $entity, 'typeId' => $element->getId())),
            'element' => $element,
            'components' => $components,
            'entity' => $entity,
            'slug' => $slug
        ]);
    }

    /**
     * @Route("/details_preview/{entitySlug}/{slug}", name="single_preview", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param string $entitySlug
     * @param $slug
     * @param Redirect301Repository $redirect301Repository
     * @param Environment $environment
     * @param SeoRepository $seoRepository
     *
     * @return Response
     */
    public function singlePreview(
        Filesystem $filesystem,
        string $entitySlug,
        $slug,
        Redirect301Repository $redirect301Repository,
        Environment $environment,
        SeoRepository $seoRepository): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $entityFullName = null;
        $entity = null;
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                try {
                    $constant_reflex = new \ReflectionClassConstant($m->getName(), 'ENTITY_SLUG');
                    $constant_value = $constant_reflex->getValue();
                } catch (\ReflectionException $e) {
                    $constant_value = null;
                }
                if(null !== $constant_value) {
                    if($m->getName()::ENTITY_SLUG === $entitySlug) {
                        $entityFullName = $m->getName();
                        $entity = array_reverse(explode('\\', $entityFullName))[0];
                    }
                }
            }
        }

        if(!$entityFullName || !$entity || !$this->coreService->checkIfSingleEnable($entity)) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail Preview )");
        }

        // GET ELEMENT
        $element = $this->getDoctrine()->getRepository($entityFullName)->findOneBy(['slug' => $slug]);

        if(!$element) {
            $redirect301 = $redirect301Repository->findOneBy(['oldSlug' => $slug, 'objectType' => $entityFullName]);
            if($redirect301) {
                $element = $this->getDoctrine()->getRepository($entityFullName)->find($redirect301->getObjectId());
                $redirectUrl = $this->generateUrl('single_preview', ['entitySlug' => $entitySlug, 'slug' => $element->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail Preview )");
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $this->getDoctrine()->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $element->getId(), 'isTemp' => true, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $filesystem->exists($this->kernel->getProjectDir()."/templates/${entity}/single.html.twig")
            ? "/${entity}/single.html.twig"
            : '@AkyosCore/front/single.html.twig';
        $environment->addGlobal('global_element', $element);

        // RENDER
        return $this->render($view, [
            'seo' => $seoRepository->findOneBy(array('type' => $entity, 'typeId' => $element->getId())),
            'element' => $element,
            'components' => $components,
            'entity' => $entity,
            'slug' => $slug
        ]);
    }

    /**
     * @Route("/categorie/{entitySlug}/{category}", name="taxonomy", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @param $category
     * @return Response
     */
    public function category(
        Filesystem $filesystem,
        $entitySlug,
        $category): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $entityFullName = null;
        $entity = null;
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                try {
                    $constant_reflex = new \ReflectionClassConstant($m->getName(), 'ENTITY_SLUG');
                    $constant_value = $constant_reflex->getValue();
                } catch (\ReflectionException $e) {
                    $constant_value = null;
                }
                if(null !== $constant_value) {
                    if($m->getName()::ENTITY_SLUG === $entitySlug) {
                        $entityFullName = $m->getName();
                        $entity = array_reverse(explode('\\', $entityFullName))[0];
                    }
                }
            }
        }

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
