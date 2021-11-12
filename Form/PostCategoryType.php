<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\PostCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostCategoryType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', null, [
				'label' => 'Titre de la catégorie',
				'help' => '( Votre titre )',
			])
			->add('content', null, [
				'label' => 'Description de la catégorie',
				'help' => '( Votre description )',
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => PostCategory::class,
		]);
	}
}
