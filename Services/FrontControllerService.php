<?php

namespace Akyos\CoreBundle\Services;

use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\BuilderBundle\Entity\Component;
use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Entity\Redirect301;
use Akyos\CoreBundle\Entity\Seo;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class FrontControllerService
{
    private $em;
    /** @var RouterInterface */
    private $router;
    /** @var Filesystem */
    private $filesystem;
    /** @var KernelInterface */
    private $kernel;
    /** @var Environment */
    private $environment;
    /** @var CoreService */
    private $coreService;
    /** @var RequestStack */
    private $request;
    /** @var AuthorizationCheckerInterface */
    private $checker;

    public function __construct(
        EntityManagerInterface $em,
        RouterInterface $router,
        Filesystem $filesystem,
        KernelInterface $kernel,
        Environment $environment,
        CoreService $coreService,
        AuthorizationCheckerInterface $checker,
        RequestStack $request
    )
    {
        $this->em = $em;
        $this->router = $router;
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->environment = $environment;
        $this->coreService = $coreService;
        $this->checker = $checker;
        $this->request = $request;
    }

    public function singleAndPreview(string $entitySlug, string $slug, string $route)
    {
        // GET ENTITY NAME AND FULLNAME FROM SLUG
        [$entityFullName, $entity] = $this->coreService->getEntityAndFullString($entitySlug);

        if(!$entityFullName || !$entity || !$this->coreService->checkIfSingleEnable($entity)) {
            throw new NotFoundHttpException("Cette page n'existe pas! ( Détail )");
        }

        // GET ELEMENT
        $element = $this->em->getRepository($entityFullName)->findOneBy(['slug' => $slug]);
        if(!$element) {
            $redirect301 = $this->em->getRepository(Redirect301::class)->findOneBy(['oldSlug' => $slug, 'objectType' => $entityFullName]);
            if($redirect301) {
                $element = $this->em->getRepository($entityFullName)->find($redirect301->getObjectId());
                $redirectUrl = $this->router->generate($route, ['entitySlug' => $entitySlug, 'slug' => $element->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
            throw new NotFoundHttpException("Cette page n'existe pas! ( ${route} )");
        } elseif (property_exists($element, 'published') and !$element->getPublished() and $route !== 'single_preview') {
            if($this->checker->isGranted('ROLE_ADMIN')) {
                return new RedirectResponse($this->router->generate('single_preview', ['entitySlug' => $entitySlug, 'slug' => $slug]));
            } else {
                throw new NotFoundHttpException("Cette page n'existe pas! ( ${entity} )");
            }
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $this->em->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $element->getId(), 'isTemp' => false, 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $this->filesystem->exists($this->kernel->getProjectDir()."/templates/${entity}/single.html.twig")
            ? "/${entity}/single.html.twig"
            : '@AkyosCore/front/single.html.twig';
        $this->environment->addGlobal('global_element', $element);

        // RENDER
        return $this->environment->render($view, [
            'seo' => $this->em->getRepository(Seo::class)->findOneBy(array('type' => $entity, 'typeId' => $element->getId())),
            'element' => $element,
            'components' => $components,
            'entity' => $entity,
            'slug' => $slug
        ]);
    }

    public function pageAndPreview(string $slug, string $route)
    {
        // FIND PAGE
        $entity = 'Page';
        /** @var Page $page */
        $page = $this->em->getRepository(Page::class)->findOneBy(['slug' => $slug]) ?? $this->em->getRepository(Translation::class)->findObjectByTranslatedField('slug', $slug, Page::class);
//        dd($slug);

        if(!$page) {
            $redirect301 = $this->em->getRepository(Redirect301::class)->findOneBy(['oldSlug' => $slug, 'objectType' => Page::class]);
            if($redirect301) {
                $page = $this->em->getRepository(Page::class)->find($redirect301->getObjectId());
                $redirectUrl = $this->router->generate($route, ['slug' => $page->getSlug()]);
                return new RedirectResponse($redirectUrl, 301);
            }
            throw new NotFoundHttpException("Cette page n'existe pas! ( ${entity} )");
        } elseif (!$page->getPublished() and $route !== 'page_preview') {
            if($this->checker->isGranted('ROLE_ADMIN')) {
                return new RedirectResponse($this->router->generate('page_preview', ['slug' => $slug]));
            } else {
                throw new NotFoundHttpException("Cette page n'existe pas! ( ${entity} )");
            }
        }

        // GET COMPONENTS OR CONTENT
        $components = null;
        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            $components = $this->em->getRepository(Component::class)->findBy(['type' => $entity, 'typeId' => $page->getId(), 'isTemp' => ($route === 'page_preview'), 'parentComponent' => null], ['position' => 'ASC']);
        }

        // GET TEMPLATE
        $view = $page->getTemplate() ? '/page/'.$page->getTemplate().'.html.twig' : '@AkyosCore/front/content.html.twig';

        $this->environment->addGlobal('global_page', $page);

        return $this->environment->render($view, [
            'seo' => $this->em->getRepository(Seo::class)->findOneBy(array('type' => $entity, 'typeId' => $page->getId())),
            'page' => $page,
            'components' => $components,
            'content' => $page->getContent(),
            'slug' => $slug
        ]);
    }
}