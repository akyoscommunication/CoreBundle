<?php

namespace Akyos\CoreBundle\Form\Type\CustomFields;

use Akyos\CoreBundle\Entity\CustomFieldsGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewCustomFieldsGroupType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', TextType::class, [
				'label' => 'Nom du groupe',
				'help' => 'Donnez un titre à votre groupe de champs !',
			])
			->add('entity', ChoiceType::class, [
				'label' => 'Entité liée',
				'help' => 'A quelle entité souhaitez vous ajouter des champs ?',
				'required' => false,
				'choices' => $options['entities'],
				'choice_label' => function ($choice, $key, $value) {
					return $value;
				},
				'multiple' => false,
				'expanded' => false,
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => CustomFieldsGroup::class,
			'entities' => null,
		]);
	}
}
