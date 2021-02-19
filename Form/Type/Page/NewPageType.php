<?php

namespace Akyos\CoreBundle\Form\Type\Page;

use Akyos\CoreBundle\Entity\Page;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPageType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		/** @var Page $page */
		$page = $builder->getData();

		$builder
			->add('title', TextType::class, [
				'label' => 'Titre de la page',
			])
			->add('publishedAt', DateType::class, [
				'widget' => 'single_text',
				'label' => 'Date de publication'
			]);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Page::class,
		]);
	}
}
