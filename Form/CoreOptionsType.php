<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
            ->add('favicon', FileManagerType::class, [
                'label' => 'Favicon (format ico)',
                'required' => false,
            ])
            ->add('siteLogo', FileManagerType::class, [
                'label' => 'Logo du site',
                'required' => false,
            ])
            ->add('backMainColor', ColorType::class, [
                'label' => 'Couleur principale du back office',
                'required' => false
            ])
            ->add('hasPosts', CheckboxType::class, [
                'label' => 'Activation du blog',
                'required' => false
            ])
            ->add('hasPostDocuments', CheckboxType::class, [
                'label' => 'Activation des documents d\'articles',
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
            ->add('agencyName', TextType::class, [
                'label' => 'Nom de l\'agence',
                'required' => true
            ])
            ->add('agencyLink', UrlType::class, [
                'label' => 'Lien vers le site de l\'agence',
                'required' => true
            ])
            ->add('recaptchaPublicKey', TextType::class, [
                'label' => 'Clé publique reCaptcha',
                'required' => false
            ])
            ->add('recaptchaPrivateKey', TextType::class, [
                'label' => 'Clé privée reCaptcha',
                'required' => false
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
