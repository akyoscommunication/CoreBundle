<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Repository\PageRepository;
use Artgris\Bundle\MediaBundle\Form\Type\MediaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoreOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('siteTitle', TextType::class, [
                'label' => 'Titre du site',
                'required' => false
            ])
            ->add('favicon', MediaType::class, [
                'label' => 'Favicon (format ico)',
                'required' => false,
                'conf' => 'default'
            ])
            ->add('siteLogo', MediaType::class, [
                'label' => 'Logo du site',
                'required' => false,
                'conf' => 'default'
            ])
            ->add('backMainColor', ColorType::class, [
                'label' => 'Couleur principale du back office',
                'required' => false
            ])
            ->add('hasPosts', CheckboxType::class, [
                'label' => 'Activation du blog',
                'required' => false
            ])
            ->add('homepage', EntityType::class, [
                'label' => "Page d'accueil",
                'required' => false,
                'class' => Page::class,
                'query_builder' => function (PageRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.position', 'ASC');
                },
                'choice_label' => 'title',
                'placeholder' => "Choisissez une page"
            ])
            ->add('hasArchiveEntities', ChoiceType::class, [
                'label' => 'Activer la page archive sur les entités :',
                'choices' => $options['entities'],
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                },
                'multiple' => true,
                'expanded' => true
            ])
            ->add('hasSingleEntities', ChoiceType::class, [
                'label' => 'Activer les pages single sur les entités :',
                'choices' => $options['entities'],
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                },
                'multiple' => true,
                'expanded' => true
            ])
            ->add('hasSeoEntities', ChoiceType::class, [
                'label' => 'Activer le SEO sur les entités :',
                'choices' => $options['entities'],
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                },
                'multiple' => true,
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CoreOptions::class,
            'entities' => []
        ]);
    }
}
