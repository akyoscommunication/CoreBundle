<?php

namespace Akyos\CoreBundle\Form\Type\Post;

use Akyos\CoreBundle\Entity\PostDocument;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
                'help' => 'Insérez votre titre ici',
                'required'=> true
            ])
            ->add('file', FileManagerType::class, [
                'label'=>'Document',
                'required'=> true
            ])
            ->add('content', CKEditorType::class, [
                'label'=>'Contenu'
            ])
            ->add('private', CheckboxType::class, [
                'label'=>'Est-ce que ce document doit être privé?',
                'required'=>false
            ])
            ->add('position', null, [
                'required'=> true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PostDocument::class,
        ]);
    }
}
