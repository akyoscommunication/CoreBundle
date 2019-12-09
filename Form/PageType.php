<?php

namespace Akyos\CoreBundle\Form;

use Akyos\CoreBundle\Entity\Page;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre de la page',
            ])
            ->add('published', null, [
                'label' => 'Publiée ?',
            ])
//            ->add('position')
            ->add('template', TextType::class, [
                'label' => 'Template de la page',
                'required' => false
            ])
            ->add('content', CKEditorType::class, [
                'required'    => false,
                'config'      => array(
                    'placeholder'    => "Texte",
                    'height'         => 50,
                    'entities'       => false,
                    'basicEntities'  => false,
                    'entities_greek' => false,
                    'entities_latin' => false,
                ),
                'label' => 'Contenu de la page'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
