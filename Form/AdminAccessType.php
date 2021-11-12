<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\AdminAccess;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminAccessType extends AbstractType
{
	private ContainerInterface $container;
	
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$roles = [];
		foreach ($this->container->getParameter('security.role_hierarchy.roles') as $key => $value) {
			$roles[$key] = $key;
		}
		$builder
			->add('name', TextType::class, [
				'label' => 'Nom',
				'disabled' => $options['data']->getIslocked() ? true : false
			])
			->add('roles', ChoiceType::class, [
				'choices' => $roles,
				'label' => 'Choix des rôles',
				'required' => false,
				'multiple' => true,
				'attr' => [
					'class' => 'js-select2'
				],
			]);
		if (!$options['data']->getIslocked()) {
			$builder
				->add('isLocked');
		}
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => AdminAccess::class,
		]);
	}
}
