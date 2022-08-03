<?php

namespace Akyos\CoreBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CrudHandler extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param string $success
     * @return bool
     */
    public function new(FormInterface $form, Request $request, string $success = "L'élément à bien été créé."): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            if ($success) {
                $this->addFlash('success', $success);
            }
            return true;
        }
        return false;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param string $success
     * @return bool
     */
    public function edit(FormInterface $form, Request $request, string $success = "L'élément à bien été modifié."): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            if ($success) {
                $this->addFlash('success', $success);
            }
            return true;
        }
        return false;
    }

    /**
     * @param $entity
     * @param Request $request
     * @param string $success
     * @return bool
     */
    public function delete($entity, Request $request, string $success = "L'élément à bien été supprimé."): bool
    {
        if ($this->isCsrfTokenValid('delete' . $entity->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            if ($success) {
                $this->addFlash('success', $success);
            }
            return true;
        }

        $this->addFlash('danger', "Une erreur s'est produite.");
        return false;
    }
}
