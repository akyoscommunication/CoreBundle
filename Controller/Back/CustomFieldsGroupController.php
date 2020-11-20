<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\CustomFieldsGroup;
use Akyos\CoreBundle\Form\Type\CustomFields\CustomFieldsGroupType;
use Akyos\CoreBundle\Form\Type\CustomFields\NewCustomFieldsGroupType;
use Akyos\CoreBundle\Form\Handler\CrudHandler;
use Akyos\CoreBundle\Repository\CustomFieldsGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/custom-fields-group", name="custom_fields_group_")
 */
class CustomFieldsGroupController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET","POST"})
     * @param CustomFieldsGroupRepository $customFieldsGroupRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @param CrudHandler $crudHandler
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function index(CustomFieldsGroupRepository $customFieldsGroupRepository, PaginatorInterface $paginator, Request $request, CrudHandler $crudHandler, EntityManagerInterface $em): Response
    {
        $entities = [];
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            dump($m->getName());
            if(!preg_match('/Component|Option|ContactForm/i', $m->getName()) && stripos($m->getName(), 'Akyos') !== false) {
                $entities[] = $m->getName();
            }
        }

        $query = $customFieldsGroupRepository->createQueryBuilder('a');
        if($request->query->get('search')) {
            $query
                ->andWhere('a.title LIKE :keyword OR a.slug LIKE :keyword OR a.description LIKE :keyword')
                ->setParameter('keyword', '%'.$request->query->get('search').'%')
            ;
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page',1),12);

        $customFieldsGroup = new CustomFieldsGroup();
        $customFieldsGroupForm = $this->createForm(NewCustomFieldsGroupType::class, $customFieldsGroup, [
            'entities' => $entities
        ]);

        if ($crudHandler->new($customFieldsGroupForm, $request)) {
            return $this->redirectToRoute('custom_fields_group_edit', ['id' => $customFieldsGroup->getId()]);
        }

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Groupes de champs',
            'entity' => 'CustomFieldsGroup',
            'route' => 'custom_fields_group',
            'formModal' => $customFieldsGroupForm->createView(),
            'fields' => array(
                'ID' => 'Id',
                'Nom' => 'Title',
                'Entité' => 'Entity',
            ),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     * @param Request $request
     *
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $entities = [];
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            dump($m->getName());
            if(!preg_match('/Component|Option|ContactForm/i', $m->getName()) && stripos($m->getName(), 'Akyos') !== false) {
                $entities[] = $m->getName();
            }
        }

        $customFieldsGroup = new CustomFieldsGroup();
        $form = $this->createForm(NewCustomFieldsGroupType::class, $customFieldsGroup, [
            'entities' => $entities
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($customFieldsGroup);
            $entityManager->flush();

            return $this->redirectToRoute('custom_fields_group_index');
        }

        return $this->render('@AkyosCore/crud/new.html.twig', [
            'el' => $customFieldsGroup,
            'title' => 'Groupe de champs',
            'entity' => 'CustomFieldsGroup',
            'route' => 'custom_fields_group',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param CustomFieldsGroup $customFieldsGroup
     *
     * @return Response
     */
    public function edit(Request $request, CustomFieldsGroup $customFieldsGroup, EntityManagerInterface $em): Response
    {
        $akyosEntities = [];
        $entities = [];
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|ContactForm/i', $m->getName()) && stripos($m->getName(), 'Akyos') !== false) {
                $akyosEntities[] = $m->getName();
            }
            if(!preg_match('/Component|Option|ContactForm/i', $m->getName())) {
                $entities[] = $m->getName();
            }
        }

        $form = $this->createForm(CustomFieldsGroupType::class, $customFieldsGroup, [
            'akyosEntities' => $akyosEntities,
            'entities' => $entities,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('custom_fields_group_index');
        }

        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $customFieldsGroup,
            'title' => 'Groupe de champs',
            'entity' => 'CustomFieldsGroup',
            'route' => 'custom_fields_group',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param CustomFieldsGroup $customFieldsGroup
     *
     * @return Response
     */
    public function delete(Request $request, CustomFieldsGroup $customFieldsGroup): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customFieldsGroup->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($customFieldsGroup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('custom_fields_group_index');
    }
}
