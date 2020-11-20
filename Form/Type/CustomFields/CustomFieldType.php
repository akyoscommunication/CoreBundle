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
                'help' => 'Donnes un petit nom à ton champ (ce sera le label du champ custom, le client le verra, donc fais pas trop le con!)',
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'help' => 'On a besoin d\'un slug pour pouvoir récupérer la value dans le code',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de champ',
                'help' => 'Tu veux quoi comme input mec ?',
                'choices'  => [
                    'Champ texte'    => 'text',
                    'CKEditor'       => 'textarea_html',
                    'Zone de texte'  => 'textarea',
                    'Image'          => 'image',
                    'Galerie d\images' => 'gallery',
                    'Téléphone'       => 'tel',
                    'Mail'           => 'mail',
                    'Lien interne'   => 'pagelink',
                    'Lien interne article' => 'postlink',
                    'Lien externe'   => 'link',
                    'Nombre'         => 'int',
                    'Select'        => 'select',
                    'Booléen'       => 'bool',
                    'Entité'        => 'entity',
                    'Couleur'        => 'color',
                ],
            ])
            ->add('entity', ChoiceType::class, [
                'label' => 'Entité liée',
                'help' => 'Si tu as choisi "entité", faut me dire laquelle c\'est stp',
                'required' => false,
                'choices' => $options['entities'],
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                },
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'help' => 'De quoi ça s\'agit ?',
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Obligatoire ?',
                'help' => 'Si j\'en ai rien à foutre, est-ce que je peux zapper le champ ou est-ce que je dois quand même mettre un truc ?',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomField::class,
            'entities' => null,
        ]);
    }
}
