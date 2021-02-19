<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\Menu;
use Akyos\CoreBundle\Entity\MenuArea;
use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Repository\MenuAreaRepository;
use Akyos\CoreBundle\Repository\MenuItemRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', null, [
				'label' => 'Titre du menu',
				'help' => 'Insérez votre titre ici'
			])
			->add('menuArea', EntityType::class, [
				'label' => 'Zone de menu',
				'help' => 'Ce menu doit-il apparaître dans une zone de menu ?',
				'required' => false,
				'class' => MenuArea::class,
				'query_builder' => function (MenuAreaRepository $er) {
					return $er->createQueryBuilder('ma')
						->orderBy('ma.name', 'ASC');
				},
				'choice_label' => 'name',
				'placeholder' => "Choisissez une zone"
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Menu::class,
		]);
	}
}
