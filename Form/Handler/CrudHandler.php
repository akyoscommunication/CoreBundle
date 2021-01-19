<?php

namespace Akyos\CoreBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

class CrudHandler extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function new(FormInterface $form, Request $request, $success = "L'élément à bien été créé."): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            $this->em->persist($entity);
            $this->em->flush();

            if ($success) {
                $this->addFlash('success', $success);
            }
            return true;
        }
        return false;
    }

    public function edit(FormInterface $form, Request $request, $success = "L'élément à bien été modifié."): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            if ($success) {
                $this->addFlash('success', $success);
            }
            return true;
        }
        return false;
    }

    public function delete($entity, Request $request, $success = "L'élément à bien été supprimé."): bool
    {
        if ($this->isCsrfTokenValid('delete'.$entity->getId(), $request->request->get('_token'))) {
            $this->em->remove($entity);
            $this->em->flush();

            if ($success) {
                $this->addFlash('success', $success);
            }
            return true;
        } else {
            $this->addFlash('danger', "Une erreur s'est produite.");
            return false;
        }
    }
}
