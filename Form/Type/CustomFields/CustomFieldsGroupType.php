<?php

namespace Akyos\CoreBundle\Form\Type\CustomFields;

use Akyos\CoreBundle\Entity\CustomFieldsGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldsGroupType extends AbstractType
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
                'choices' => $options['akyosEntities'],
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                },
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('customFields', CollectionType::class, [
                'entry_type' => CustomFieldType::class,
                'entry_options' => [
                    'label' => false,
                    'entities' => $options['entities'],
                    'attr' => [
                        'class' => 'card-header__title'
                    ]
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'attr' => [
                    'class' => 'collection_prototype'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomFieldsGroup::class,
            'akyosEntities' => null,
            'entities' => null,
        ]);
    }
}
