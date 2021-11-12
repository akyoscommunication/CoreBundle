<?php

namespace Akyos\CoreBundle\Service;

use Akyos\CoreBundle\Entity\AdminAccess;
use Akyos\CoreBundle\Repository\AdminAccessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ExtendAdminAccess
{
	private AdminAccessRepository $adminAccessRepository;
	private EntityManagerInterface $entityManager;
	
	public function __construct(AdminAccessRepository $adminAccessRepository, EntityManagerInterface $entityManager)
	{
		$this->adminAccessRepository = $adminAccessRepository;
		$this->entityManager = $entityManager;
	}
	
	public function setDefaults(): Response
    {
		if (!$this->adminAccessRepository->findOneBy(['name' => "Pages"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName('Pages')
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		if (!$this->adminAccessRepository->findOneBy(['name' => "Liste des articles"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName('Liste des articles')
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Catégories d'articles"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Catégories d'articles")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Etiquette d'articles"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Etiquette d'articles")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Liste de menus"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Liste de menus")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Zones de menus"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Zones de menus")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Eléments du menu"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Eléments du menu")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Accueil"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Accueil")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Utilisateurs"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Utilisateurs")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Exports"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Exports")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Options du site"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Options du site")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Catégorie d'options du site"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Catégorie d'options du site")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Paramètres"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Paramètres")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Rgpd"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Rgpd")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Champs personnalisés"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Champs personnalisés")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Profil"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Profil")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		if (!$this->adminAccessRepository->findOneBy(['name' => "Gestion des droits"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Gestion des droits")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneBy(['name' => "Options du Core"])) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Options du Core")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		return new Response('true');
	}
}