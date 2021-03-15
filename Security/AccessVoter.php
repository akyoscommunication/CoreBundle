<?php

namespace Akyos\CoreBundle\Security;

use Akyos\CoreBundle\Entity\AdminAccess;
use Akyos\CoreBundle\Repository\AdminAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AccessVoter extends Voter
{
	private $security;
	private $adminAccessRepository;

	public function __construct(Security $security, AdminAccessRepository $adminAccessRepository)
	{
		$this->security = $security;
		$this->adminAccessRepository = $adminAccessRepository;
	}

	protected function supports($attribute, $subject): bool
	{
		if ($this->adminAccessRepository->findOneBySlug($attribute) and $this->security->getUser()) {
			return true;
		}
		return false;
	}

	protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
	{
		$role = $this->adminAccessRepository->findOneBySlug($attribute);
		if ($role) {
			$authorizedRoles = $role->getRoles();
			if (!empty($authorizedRoles)) {
				if ($this->security->isGranted($authorizedRoles)) {
					return true;
				}
				return false;
			}
			return true;
		}
		return true;
	}
}