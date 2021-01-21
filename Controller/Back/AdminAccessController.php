<?php
namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\AdminAccess;
use Akyos\CoreBundle\Form\AdminAccessType;
use Akyos\CoreBundle\Repository\AdminAccessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class AdminAccessController
 * @package Akyos\CoreBundle\Controller\Back
 * @Route("/admin/gestion-des-droits", name="admin_access_")
 */
class AdminAccessController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param AdminAccessRepository $accessRepository
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(AdminAccessRepository $accessRepository, Request $request, PaginatorInterface $paginator)
    {
        $finder = new Finder();
        $finder->depth('== 0');
        foreach ($finder->directories()->in($this->getParameter('kernel.project_dir').'/lib') as $bundleDirectory) {
            if(class_exists('Akyos\\' . $bundleDirectory->getFilename() . '\Service\ExtendAdminAccess') &&
                method_exists('Akyos\\' . $bundleDirectory->getFilename() . '\Service\ExtendAdminAccess', 'setDefaults')
            ) {
                $this->forward('Akyos\\' . $bundleDirectory->getFilename() . '\Service\ExtendAdminAccess::setDefaults', []);
            }
        }
        $query = $accessRepository->searchByName($request->query->get('search'));
        $els = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            16
        );
        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Accès admin',
            'entity' => 'Akyos\CoreBundle\Entity\AdminAccess',
            'route' => 'admin_access',
            'search'=>true,
            'fields' => array(
                'Id' => 'Id',
                'Nom'=> 'Name',
                'Slug'=>'Slug',
                'Roles'=>'Roles'
            ),
        ]);
    }

    /**
     * @Route("/nouveau", name="new")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager)
    {
        $adminAccess = new AdminAccess();
        $form = $this->createForm(AdminAccessType::class, $adminAccess);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($adminAccess);
            $entityManager->flush();
            return $this->redirectToRoute('admin_access_index');
        }
        return $this->render('@AkyosCore/crud/new.html.twig', [
            'form'=>$form->createView(),
            'el' => $adminAccess,
            'title' => 'Accès admin',
            'entity' => 'AdminAccess',
            'route' => 'admin_access',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @param AdminAccess $adminAccess
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     */
    public function edit(AdminAccess $adminAccess, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(AdminAccessType::class, $adminAccess);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($adminAccess);
            $entityManager->flush();
            return $this->redirectToRoute('admin_access_edit', [
                'id'=>$adminAccess->getId()
            ]);
        }
        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $adminAccess,
            'title' => 'Accès admin ' . $adminAccess->getName() ,
            'entity' => 'AdminAccess',
            'route' => 'admin_access',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param AdminAccess $adminAccess
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    public function delete(AdminAccess $adminAccess, Request $request, EntityManagerInterface $entityManager)
    {
        if(!$adminAccess->getIsLocked())
        {
            if ($this->isCsrfTokenValid('delete'.$adminAccess->getId(), $request->request->get('_token'))) {
                $entityManager->remove($adminAccess);
                $entityManager->flush();
                return $this->redirectToRoute('admin_access_index');
            }
            throw new InvalidCsrfTokenException('impossible de supprimer, csrf invalide');
        }
        throw new \Exception('Supprimer cet objet est interdit');
    }
}