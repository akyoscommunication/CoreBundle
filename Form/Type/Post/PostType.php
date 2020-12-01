<?php

namespace Akyos\CoreBundle\Form\Type\Post;

use Akyos\CoreBundle\Entity\Post;
use Akyos\CoreBundle\Entity\PostCategory;
use Akyos\CoreBundle\Entity\PostTag;
use Akyos\CoreBundle\Form\Type\CustomFields\ACFType;
use Akyos\FileManagerBundle\Form\Type\FileManagerCollectionType;
use Akyos\FileManagerBundle\Form\Type\FileManagerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
                'help' => 'Insérez votre titre ici',
            ])
            ->add('slug', null, [
                'label' => "Slug de l'article",
                'help' => '( mon-slug )',
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu de l\'article',
                'required'    => false,
                'config'      => [
                    'placeholder'    => "Texte",
                    'height'         => 50,
                    'entities'       => false,
                    'basicEntities'  => false,
                    'entities_greek' => false,
                    'entities_latin' => false,
                ],
            ])
            ->add('published', null, [
                'label' => 'Publié ?',
                'help' => '( Un article non publié n\'apparaîtra pas sur le site )',
            ])
            ->add('thumbnail', FileManagerType::class, [
                'label' => 'Image à la une',
            ])
            ->add('thumbnailArchive', FileManagerType::class, [
                'label' => 'Image à la une ( miniature sur le listing des actualités )',
            ])
            ->add('gallery', FileManagerCollectionType::class, [
                'label' => 'Galerie d\'images',
            ])
            ->add('postCategories', null, [
                'by_reference' => false,
                'attr' => ['class' => 'form-control js-select2'],
                'label' => 'Catégorie(s) liée(s)',
                'help' => '( Sélectionnez les catégories de l\'article )',
            ])
            ->add('postTags', null, [
                'by_reference' => false,
                'attr' => ['class' => 'form-control js-select2'],
                'label' => 'Étiquettes(s) associée(s)',
                'help' => '( Sélectionnez les étiquettes de l\'article )',
            ])
            ->add('createdAt', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de publication'
            ])
            ->add('customFields', ACFType::class, [
                'entity' => 'Akyos\\CoreBundle\\Entity\\Post',
                'object_id' => $options['data']->getId(),
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
