<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\User;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserType extends AbstractType
{
	private AuthorizationCheckerInterface $authorizationChecker;
	private ContainerInterface $container;
	
	public function __construct(AuthorizationCheckerInterface $authorizationChecker, ContainerInterface $container)
	{
		$this->authorizationChecker = $authorizationChecker;
		$this->container = $container;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$roles = $this->container->getParameter('user_roles');
		if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
			unset($roles['Super Admin']);
		}
		if (!$this->authorizationChecker->isGranted('ROLE_AKYOS')) {
			if (array_key_exists('ROLE_AKYOS', $roles)) {
				unset($roles['Akyos']);
			}
		}
		
		$builder
			->add('email', EmailType::class, [
				'label' => "E-mail",
				'help' => "Renseignez l'email de l'utilisateur"
			])
			->add('roles', ChoiceType::class, [
				'label' => "Rôle de l'utilisateur",
				'help' => "En fonction de son rôle, l'utilisateur aura accès à différentes fonctionnalités.",
				'choices' => $roles,
				'multiple' => true,
				'expanded' => false,
				'required' => true,
				'attr' => [
					'class' => 'js-select2',
				]
			])
			->add('password', PasswordType::class, [
				'label' => "Mot de passe",
				'help' => "Renseignez un mot de passe pour l'utilisateur."
			])
			->add('image', FileManagerType::class, [
				'label' => 'Image de profil',
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}
