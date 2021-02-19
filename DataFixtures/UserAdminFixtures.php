<?php

namespace Akyos\CoreBundle\DataFixtures;

use Akyos\CoreBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAdminFixtures extends Fixture
{
	private $passwordEncoder;

	public function __construct(UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}

	public function load(ObjectManager $manager)
	{
		$user = new User();
		$user
			->setEmail("admin@akyos.fr")
			->setPassword($this->passwordEncoder->encodePassword(
				$user,
				'root'
			))
			->setRoles(["ROLE_SUPER_ADMIN"]);

		$manager->persist($user);
		$manager->flush();
	}
}
