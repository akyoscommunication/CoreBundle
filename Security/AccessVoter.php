<?php

namespace Akyos\CoreBundle\Security;

use Akyos\CoreBundle\Repository\AdminAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AccessVoter extends Voter
{
	private Security $security;
	private AdminAccessRepository $adminAccessRepository;

	public function __construct(Security $security, AdminAccessRepository $adminAccessRepository)
	{
		$this->security = $security;
		$this->adminAccessRepository = $adminAccessRepository;
	}

	protected function supports($attribute, $subject): bool
	{
        return $this->adminAccessRepository->findOneBy(['slug' => $attribute]) && $this->security->getUser();
    }

	protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
	{
		$role = $this->adminAccessRepository->findOneBy(['slug' => $attribute]);
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