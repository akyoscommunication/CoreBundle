<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\Post;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\Redirect301Repository;
use Akyos\CoreBundle\Repository\SeoRepository;
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

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Route("/", name="home", methods={"GET","POST"})
     * @param CoreOptionsRepository $coreOptionsRepository
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @param Environment $environment
     * @return Response
     */
    public function home(CoreOptionsRepository $coreOptionsRepository, PageRepository $pageRepository, SeoRepository $seoRepository, Environment $environment): Response
    {
        // FIND HOMEPAGE
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            $homePage = $coreOptions[0]->getHomepage();
        } else {
            $homePage = $pageRepository->findOneBy([], ['position' => "ASC"]);
        }

        if(!$homePage) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Accueil )");
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

        $environment->addGlobal('global_page', $homePage);

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
     * @Route("/{slug}", name="page", methods={"GET","POST"}, requirements={"slug"="^(?!admin\/|app\/|recaptcha\/|archive\/|details\/|details_preview\/|categorie\/|file-manager\/).+"})
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @param Redirect301Repository $redirect301Repository
     * @param $slug
     * @param Environment $environment
     * @return Response
     */
    public function page(PageRepository $pageRepository, SeoRepository $seoRepository, Redirect301Repository $redirect301Repository, $slug, Environment $environment): Response
    {
        // FIND PAGE
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if(!$page) {
            $redirect301 = $redirect301Repository->findOneBy(['oldSlug' => $slug, 'objectType' => 'Akyos\CoreBundle\Entity\Page']);
            if($redirect301) {
                $page = $pageRepository->find($redirect301->getObjectId());
                $redirectUrl = $this->generateUrl('page', ['slug' => $page->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
        }

        if(!$page) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Page )");
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

        $environment->addGlobal('global_page', $page);

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
            throw $this->createNotFoundException("Cette page n'existe pas! ( Page )");
        }
    }

    /**
     * @Route("page_preview/{slug}", name="page_preview", methods={"GET","POST"}, requirements={"slug"="^(?!admin|app|archive|details|details_preview|categorie).+"})
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @param Redirect301Repository $redirect301Repository
     * @param $slug
     * @param Environment $environment
     * @return Response
     */
    public function pagePreview(PageRepository $pageRepository, SeoRepository $seoRepository, Redirect301Repository $redirect301Repository, $slug, Environment $environment): Response
    {
        // FIND PAGE
        $page = $pageRepository->findOneBy(['slug' => $slug]);

        if(!$page) {
            $redirect301 = $redirect301Repository->findOneBy(['oldSlug' => $slug, 'objectType' => 'Akyos\CoreBundle\Entity\Page']);
            if($redirect301) {
                $page = $pageRepository->find($redirect301->getObjectId());
                $redirectUrl = $this->generateUrl('page_preview', ['slug' => $page->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
        }

        if(!$page) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Page Preview )");
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

        $environment->addGlobal('global_page', $page);

        if ($page->getPublished()) {
            // RENDER
            return $this->render($view, [
                'seo' => $seo,
                'page' => $page,
                'components' => $components,
                'slug' => $slug
            ]);
        } else {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Page Preview )");
        }
    }

    /**
     * @Route("/archive/{entitySlug}", name="archive", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @return Response
     */
    public function archive(Filesystem $filesystem, $entitySlug): Response
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
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @param $slug
     * @param SeoRepository $seoRepository
     * @param Redirect301Repository $redirect301Repository
     * @param Environment $environment
     * @return Response
     */
    public function single(Filesystem $filesystem, $entitySlug, $slug, SeoRepository $seoRepository, Redirect301Repository $redirect301Repository, Environment $environment): Response
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
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail )");
        }

        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfSingleEnable', ['entity' => $entity])->getContent() === "false") {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail )");
        }

        // GET ELEMENT
        $element = $this->getDoctrine()->getRepository($entityFullName)->findOneBy(['slug' => $slug]);

        if (property_exists($element, 'published') and !$element->getPublished()) {
            return $this->redirectToRoute('single_preview', ['entitySlug' => $entitySlug, 'slug' => $slug]);
        }

        if(!$element) {
            $redirect301 = $redirect301Repository->findOneBy(['oldSlug' => $slug, 'objectType' => $entityFullName]);
            if($redirect301) {
                $element = $this->getDoctrine()->getRepository($entityFullName)->find($redirect301->getObjectId());
                $redirectUrl = $this->generateUrl('single', ['entitySlug' => $entitySlug, 'slug' => $element->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
        }

        if(!$element) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail )");
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
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail )");
        }

        $environment->addGlobal('global_element', $element);

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
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @param $slug
     * @param Redirect301Repository $redirect301Repository
     * @param Environment $environment
     * @param SeoRepository $seoRepository
     *
     * @return Response
     */
    public function singlePreview(Filesystem $filesystem, string $entitySlug, $slug, Redirect301Repository $redirect301Repository, Environment $environment, SeoRepository $seoRepository): Response
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
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail Preview )");
        }

        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfSingleEnable', ['entity' => $entity])->getContent() === "false") {
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
        }

        if(!$element) {
            throw $this->createNotFoundException("Cette page n'existe pas! ( Détail Preview )");
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

        $environment->addGlobal('global_element', $element);

        // GET SEO
        $seo = $seoRepository->findOneBy(array('type' => $entity, 'typeId' => $element->getId()));

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
     * @Route("/categorie/{entitySlug}/{category}", name="taxonomy", methods={"GET","POST"})
     * @param Filesystem $filesystem
     * @param $entitySlug
     * @param $category
     * @return Response
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
        if($filesystem->exists($this->kernel->getProjectDir().'/templates/'.$entity.'/category.html.twig')) {
            $view = '/'.$entity.'/category.html.twig';
        } else {
            $view = '@AkyosCore/front/category.html.twig';
        }

        // RENDER
        return $this->render($view, [
            'elements' => $elements,
            'entity' => $entity,
            'slug' => $category,
            'category' => $categoryObject
        ]);
    }
}
