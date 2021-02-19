<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\PostTag;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostTagType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', TextType::class, [
				'label' => 'Titre de l\'étiquette',
				'help' => 'Titre public affiché sur le site',
			])
			->add('content', CKEditorType::class, [
				'label' => 'Description de l\'étiquette',
				'help' => 'Contenu texte de description pouvant servir à ajouter du contenu sur page étiquette',
			]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => PostTag::class,
		]);
	}
}
