<?php

namespace Akyos\CoreBundle\Service;

use Akyos\CoreBundle\Entity\AdminAccess;
use Akyos\CoreBundle\Repository\AdminAccessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ExtendAdminAccess
{
    /** @var AdminAccessRepository  */
	private $adminAccessRepository;
	/** @var EntityManagerInterface  */
	private $entityManager;
	
	public function __construct(AdminAccessRepository $adminAccessRepository, EntityManagerInterface $entityManager)
	{
		$this->adminAccessRepository = $adminAccessRepository;
		$this->entityManager = $entityManager;
	}
	
	public function setDefaults()
	{
		if (!$this->adminAccessRepository->findOneByName("Pages")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName('Pages')
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		if (!$this->adminAccessRepository->findOneByName("Liste des articles")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName('Liste des articles')
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Catégories d'articles")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Catégories d'articles")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Etiquette d'articles")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Etiquette d'articles")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Liste de menus")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Liste de menus")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Zones de menus")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Zones de menus")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Eléments du menu")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Eléments du menu")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Accueil")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Accueil")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Utilisateurs")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Utilisateurs")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Exports")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Exports")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Options du site")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Options du site")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Catégorie d'options du site")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Catégorie d'options du site")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Paramètres")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Paramètres")
				->setRoles([])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Rgpd")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Rgpd")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Champs personnalisés")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Champs personnalisés")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		
		if (!$this->adminAccessRepository->findOneByName("Profil")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Profil")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		if (!$this->adminAccessRepository->findOneByName("Gestion des droits")) {
			$adminAccess = new AdminAccess();
			$adminAccess
				->setName("Gestion des droits")
				->setRoles(["ROLE_AKYOS"])
				->setIsLocked(true);
			$this->entityManager->persist($adminAccess);
			$this->entityManager->flush();
		}
		
		if (!$this->adminAccessRepository->findOneByName("Options du Core")) {
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