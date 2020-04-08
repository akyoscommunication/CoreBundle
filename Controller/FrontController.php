<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\Post;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    protected $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Route("/", name="home", methods={"GET","POST"})
     */
    public function home(CoreOptionsRepository $coreOptionsRepository, PageRepository $pageRepository, SeoRepository $seoRepository): Response
    {
        // FIND HOMEPAGE
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            $homePage = $coreOptions[0]->getHomepage();
        } else {
            $homePage = $pageRepository->findOneBy([], ['position' => "ASC"]);
        }

        if(!$homePage) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Page'])->getContent() === "true") {
            $components = $this->getDoctrine()->getRepository('Akyos\\BuilderBundle\\Entity\\Component')->findBy(['type' => 'Page', 'typeId' => $homePage->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
        }
        $content = $homePage->getContent();

        // GET TEMPLATE
        if($homePage->getTemplate()) {
            $view = '/home/'.$homePage->getTemplate().'.html.twig';
        } else {
            $view = '@AkyosCore/front/content.html.twig';
        }

        // GET SEO
        $seo = $seoRepository->findOneBy(array('type' => 'Page', 'typeId' => $homePage->getId()));

        // RENDER
        return $this->render($view, [
            'seo' => $seo,
            'page' => $homePage,
            'components' => $components,
            'content' => $content,
            'slug' => 'accueil'
        ]);
    }

    /**
     * @Route("/{slug}", name="page", methods={"GET","POST"}, requirements={"slug"="^(?!admin\/|app\/|archive\/|details\/|categorie\/|file-manager\/).+"})
     */
    public function page(PageRepository $pageRepository, SeoRepository $seoRepository, $slug): Response
    {
        // FIND PAGE
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if(!$page) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Page'])->getContent() === "true") {
            $components = $this->getDoctrine()->getRepository('Akyos\\BuilderBundle\\Entity\\Component')->findBy(['type' => 'Page', 'typeId' => $page->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
        }
        $content = $page->getContent();

        // GET TEMPLATE
        if($page->getTemplate()) {
            $view = '/page/'.$page->getTemplate().'.html.twig';
        } else {
            $view = '@AkyosCore/front/content.html.twig';
        }

        // GET SEO
        $seo = $seoRepository->findOneBy(array('type' => 'Page', 'typeId' => $page->getId()));

        if ($page->getPublished()) {
            // RENDER
            return $this->render($view, [
                'seo' => $seo,
                'page' => $page,
                'components' => $components,
                'content' => $content,
                'slug' => $slug
            ]);
        } else {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }
    }

    /**
     * @Route("page_preview/{slug}", name="page_preview", methods={"GET","POST"}, requirements={"slug"="^(?!admin|app|archive|details|categorie).+"})
     */
    public function pagePreview(PageRepository $pageRepository, SeoRepository $seoRepository, $slug): Response
    {
        // FIND PAGE
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if(!$page) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Page'])->getContent() === "true") {
            $components = $this->getDoctrine()->getRepository('Akyos\\BuilderBundle\\Entity\\Component')->findBy(['type' => 'Page', 'typeId' => $page->getId(), 'isTemp' => true, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        if($page->getTemplate()) {
            $view = '/page/'.$page->getTemplate().'.html.twig';
        } else {
            $view = '@AkyosCore/front/content.html.twig';
        }

        // GET SEO
        $seo = $seoRepository->findOneBy(array('type' => 'Page', 'typeId' => $page->getId()));

        if ($page->getPublished()) {
            // RENDER
            return $this->render($view, [
                'seo' => $seo,
                'page' => $page,
                'components' => $components,
                'slug' => $slug
            ]);
        } else {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }
    }

    /**
     * @Route("/archive/{entitySlug}", name="archive", methods={"GET","POST"})
     */
    public function archive(Filesystem $filesystem, $entitySlug): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $entityFullName = null;
        $entity = null;
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            dump($m);
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName()) && ($m['namespace'] === 'App\\Entity' || strpos($m->namespace, 'Akyos') !== false )) {
                if($m->getName()::ENTITY_SLUG === $entitySlug) {
                    $entityFullName = $m->getName();
                    $entity = array_reverse(explode('\\', $entityFullName))[0];
                }
            }
        }

        if(!$entityFullName || !$entity) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfArchiveEnable', ['entity' => $entity])->getContent() === "false") {
            throw $this->createNotFoundException('La page archive n\'est pas activée pour cette entité ');
        }

        // GET ELEMENTS
        $elements = $this->getDoctrine()->getRepository($entityFullName)->findAll();
        if(!$elements) {
            throw $this->createNotFoundException('Aucun élément pour cette entité! ');
        }

        // GET TEMPLATE
        if($filesystem->exists($this->kernel->getProjectDir().'/templates/'.$entity.'/archive.html.twig')) {
            $view = '/'.$entity.'/archive.html.twig';
        } else {
            $view = '@AkyosCore/front/archive.html.twig';
        }

        // RENDER
        return $this->render($view, [
            'elements' => $elements,
            'entity' => $entity,
            'slug' => $entitySlug
        ]);
    }

    /**
     * @Route("/details/{entitySlug}/{slug}", name="single", methods={"GET","POST"})
     */
    public function single(Filesystem $filesystem, $entitySlug, $slug, SeoRepository $seoRepository): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $entityFullName = null;
        $entity = null;
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                if($m->getName()::ENTITY_SLUG === $entitySlug) {
                    $entityFullName = $m->getName();
                    $entity = array_reverse(explode('\\', $entityFullName))[0];
                }
            }
        }

        if(!$entityFullName || !$entity) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfSingleEnable', ['entity' => $entity])->getContent() === "false") {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // GET ELEMENT
        $element = $this->getDoctrine()->getRepository($entityFullName)->findOneBy(['slug' => $slug]);
        if(!$element) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => $entity])->getContent() === "true") {
            $components = $this->getDoctrine()->getRepository('Akyos\\BuilderBundle\\Entity\\Component')->findBy(['type' => $entity, 'typeId' => $element->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        if($filesystem->exists($this->kernel->getProjectDir().'/templates/'.$entity.'/single.html.twig')) {
            $view = '/'.$entity.'/single.html.twig';
        } else {
            $view = '@AkyosCore/front/single.html.twig';
        }

        // GET SEO
        $seo = $seoRepository->findOneBy(array('type' => $entity, 'typeId' => $element->getId()));

        if ($element instanceof Post && !$element->getPublished()) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }
        // RENDER
        return $this->render($view, [
            'seo' => $seo,
            'element' => $element,
            'components' => $components,
            'entity' => $entity,
            'slug' => $slug
        ]);
    }

    /**
     * @Route("/details_preview/{entitySlug}/{slug}", name="single_preview", methods={"GET","POST"})
     */
    public function singlePreview(Filesystem $filesystem, $entitySlug, $slug): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $entityFullName = null;
        $entity = null;
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                if($m->getName()::ENTITY_SLUG === $entitySlug) {
                    $entityFullName = $m->getName();
                    $entity = array_reverse(explode('\\', $entityFullName))[0];
                }
            }
        }

        if(!$entityFullName || !$entity) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfSingleEnable', ['entity' => $entity])->getContent() === "false") {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // GET ELEMENT
        $element = $this->getDoctrine()->getRepository($entityFullName)->findOneBy(['slug' => $slug]);
        if(!$element) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => $entity])->getContent() === "true") {
            $components = $this->getDoctrine()->getRepository('Akyos\\BuilderBundle\\Entity\\Component')->findBy(['type' => $entity, 'typeId' => $element->getId(), 'isTemp' => true, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        if($filesystem->exists($this->kernel->getProjectDir().'/templates/'.$entity.'/single.html.twig')) {
            $view = '/'.$entity.'/single.html.twig';
        } else {
            $view = '@AkyosCore/front/single.html.twig';
        }

        // RENDER
        return $this->render($view, [
            'element' => $element,
            'components' => $components,
            'entity' => $entity
        ]);
    }

    /**
     * @Route("/categorie/{entitySlug}/{category}", name="taxonomy", methods={"GET","POST"})
     */
    public function category(Filesystem $filesystem, $entitySlug, $category): Response
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        $entityFullName = null;
        $entity = null;
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                if($m->getName()::ENTITY_SLUG === $entitySlug) {
                    $entityFullName = $m->getName();
                    $entity = array_reverse(explode('\\', $entityFullName))[0];
                }
            }
        }

        if(!$entityFullName || !$entity) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // GET CATEGORY FULLNAME FROM ENTITY SLUG
        $categoryFullName = null;
        foreach ($meta as $m) {
            if(preg_match('/'.$entity.'Category$/i', $m->getName())) {
                $categoryFullName = $m->getName();
            }
        }

        if(!$categoryFullName) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }

        // FIND ELEMENTS FROM CATEGORY OBJECT
        $categoryObject = $this->getDoctrine()->getRepository($categoryFullName)->findOneBy(['slug' => $category]);
        if(!$categoryObject) {
            throw $this->createNotFoundException('Cette page n\'existe pas! ');
        }
        if(substr($entity, -1) === "y") {
            $getter = 'get'.substr(ucfirst($entity), 0,strlen($entity) - 1).'ies';
        } else {
            $getter = 'get'.ucfirst($entity).'s';
        }
        $elements = $categoryObject->$getter();

        // GET TEMPLATE
        if($filesystem->exists($this->kernel->getProjectDir().'/templates/'.$entity.'/category.html.twig')) {
            $view = '/'.$entity.'/category.html.twig';
        } else {
            $view = '@AkyosCore/front/category.html.twig';
        }

        // RENDER
        return $this->render($view, [
            'elements' => $elements,
            'entity' => $entity
        ]);
    }
}
