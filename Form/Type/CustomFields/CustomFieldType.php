<?php

namespace Akyos\CoreBundle\Form\Type\CustomFields;

use Akyos\CoreBundle\Entity\CustomField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', TextType::class, [
				'label' => 'Nom du champ',
				'help' => 'Ce sera le label du champ',
			])
			->add('slug', TextType::class, [
				'label' => 'Slug',
				'help' => 'On a besoin d\'un slug pour pouvoir récupérer la valeur dans le code: c\'est le nom sans majusculesn espaces, ni caractères spéciaux',
			])
			->add('type', ChoiceType::class, [
				'label' => 'Type de champ',
				'help' => 'Quel type de champ faut-il afficher ?',
				'choices' => [
					'Champ texte' => 'text',
					'CKEditor' => 'textarea_html',
					'Zone de texte' => 'textarea',
					'Image' => 'image',
					'Galerie d\images' => 'gallery',
					'Téléphone' => 'tel',
					'Mail' => 'mail',
					'Lien interne' => 'pagelink',
					'Lien interne article' => 'postlink',
					'Lien externe' => 'link',
					'Nombre' => 'int',
					'Select' => 'select',
					'Booléen' => 'bool',
					'Entité' => 'entity',
					'Couleur' => 'color',
				],
			])
			->add('entity', ChoiceType::class, [
				'label' => 'Entité liée',
				'help' => 'Si vous avez choisi "entité", précisez laquelle',
				'required' => false,
				'choices' => $options['entities'],
				'choice_label' => function ($choice, $key, $value) {
					return $value;
				},
				'multiple' => false,
				'expanded' => false,
			])
			->add('options', TextType::class, [
				'label' => 'Options',
				'required' => false,
				'help' => "On en a besoin si tu veux mettres tes choix d'un select, sépare les par des | mon gars et ensuite par un ; pour séparé le nom du choix et sa value si tu veux que ça marche. La première string sera le placeholder donc fait pas le con.",
			])
			->add('description', TextareaType::class, [
				'label' => 'Description',
				'help' => 'Description du champ: à quoi sert-il ?',
			])
			->add('isRequired', CheckboxType::class, [
				'label' => 'Obligatoire ?',
				'help' => 'Est-ce qu\'il faut obligatoirement remplir ce champ ? ',
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => CustomField::class,
			'entities' => null,
		]);
	}
}
